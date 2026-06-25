<?php

namespace Modules\Users\Http\Controllers\SelfService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use App\Services\TsuErrorHandlerService;

use App\Models\MasterLembur;
use App\Models\LemburKaryawan;
use App\Models\DataDosenTendik;
use App\Models\MasterUnit;
use App\Models\KaryawanJabatanStruktural;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\MiddlewareController;

class LemburController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware('auth');
        // $this->registerPermissions('users:lembur');
    }

    /**
     * Get current user's Dosen/Tendik profile
     */
    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    public function index()
    {
        if (Session::has('tmp')) {
            Session::forget('tmp');
        }

        $profile = $this->getCurrentProfile();
        
        $mlembur = MasterLembur::where('is_active', '1')->get();

        // Get list of SDM for dropdown selection
        $listSdm = DataDosenTendik::whereNotNull('nama')
                        ->where('tipe_karyawan', 'Tendik')
                        ->where(function ($q) {
                            $q->where('posisi', 'like', '%SDM%')
                              ->orWhere('posisi', 'like', '%Sumber Daya Manusia%');
                        })
                        ->orderBy('nama', 'asc')
                        ->get(['id', 'nama', 'nik']);

        $isAtasan = false;
        $namaAtasan = 'Belum/Tidak Ada Atasan (Silakan hubungi SDM)';
        if ($profile) {
            $isAtasan = LemburKaryawan::where('id_atasan', $profile->id)->exists();
            if ($profile->unit_id) {
                $unit = MasterUnit::find($profile->unit_id);
                if ($unit) {
                    $id_atasan = $this->findAtasanId($unit, $profile->id);
                    if ($id_atasan) {
                        $atasan = DataDosenTendik::find($id_atasan);
                        if ($atasan) {
                            $namaAtasan = $atasan->nama;
                        }
                    }
                }
            }
        }

        $data = [
            'title'   => 'Lembur Karyawan',
            'menu'    => 'dashboard',
            'mlembur' => $mlembur,
            'profile' => $profile,
            'karyawans' => $listSdm,
            'isAtasan'  => $isAtasan,
            'namaAtasan' => $namaAtasan
        ];

        return view('users::lembur.index', $data);
    }

    public function store(Request $req)
    {
        // $this->guardStore(null, 'users:lembur');

        try {
            $validator = Validator::make($req->all(), [
                'id_mlembur' => 'required',
                'tanggal1' => 'required|date',
                'tanggal2' => 'required|date|after_or_equal:tanggal1',
                'alasan' => 'required',
                'id_hrd' => 'required',
                'bukti_kegiatan' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ], [
                'id_mlembur.required' => 'Jenis Lembur Tidak Boleh Kosong',
                'tanggal1.required' => 'Tanggal & Jam Mulai Tidak Boleh Kosong',
                'tanggal2.required' => 'Tanggal & Jam Selesai Tidak Boleh Kosong',
                'tanggal2.after_or_equal' => 'Waktu Selesai harus setelah Waktu Mulai',
                'alasan.required' => 'Alasan/Keterangan Tidak Boleh Kosong',
                'id_hrd.required' => 'Pilihan SDM Tidak Boleh Kosong',
                'bukti_kegiatan.required' => 'Bukti Kegiatan Tidak Boleh Kosong',
                'bukti_kegiatan.mimes' => 'Format file harus JPG, PNG, atau PDF',
                'bukti_kegiatan.max' => 'Ukuran file maksimal 2MB',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first());
            }

            $profile = $this->getCurrentProfile();
            if (!$profile) {
                 return $this->sendError('Profil karyawan tidak ditemukan.');
            }

            if (!$profile->unit_id) {
                return $this->sendError('Anda belum ditugaskan ke Unit manapun. Silakan hubungi HRD.');
            }

            $unit = \App\Models\MasterUnit::find($profile->unit_id);
            if (!$unit) {
                return $this->sendError('Unit Anda tidak ditemukan di sistem.');
            }

            $id_atasan = $this->findAtasanId($unit, $profile->id);
            if (!$id_atasan) {
                return $this->sendError('Unit Anda (atau Unit Induk) belum memiliki Kepala Unit. Silakan hubungi SDM.');
            }

            $dataLembur = [
                'id_mlembur'      => $req->id_mlembur,
                'id_user'         => $profile->id,
                'tanggalmulai'    => $req->tanggal1,
                'tanggalselesai'  => $req->tanggal2,
                'keterangan'      => $req->alasan,
                'id_atasan'       => $id_atasan,
                'id_hrd'          => $req->id_hrd,
                'updated_by'      => $profile->nik ?? Auth::id(),
                'statusatasan'    => 'waiting',
                'statushrd'       => 'waiting',
                'created_by'      => $profile->nik ?? Auth::id()
            ];

            if ($req->hasFile('bukti_kegiatan')) {
                $file = $req->file('bukti_kegiatan');
                $timestamp = date('H-i-s');
                $dateStr = date('d-m-Y');
                $nik = $profile->nik ?? '0000';
                $extension = $file->getClientOriginalExtension();
                $filename = "Lembur_{$dateStr}_{$nik}_{$timestamp}.{$extension}";
                // $file->move(public_path('uploads/lembur/bukti'), $filename);
                $file->storeAs('public/lembur/bukti', $filename);
                $dataLembur['bukti_kegiatan'] = $filename;
            }
                
            DB::transaction(function () use ($dataLembur, &$lemburCreated) {
                $lemburCreated = LemburKaryawan::create($dataLembur);
            });

            if (isset($lemburCreated) && $id_atasan) {
                $atasanProfile = DataDosenTendik::find($id_atasan);
                if ($atasanProfile && $atasanProfile->user_id) {
                    $atasanUser = User::find($atasanProfile->user_id);
                    if ($atasanUser) {
                        $atasanUser->notify(new \App\Notifications\LemburDiajukanNotification(
                            $lemburCreated,
                            'Pengajuan lembur baru dari ' . ($profile->nama ?? 'Bawahan') . ' menunggu persetujuan Anda.'
                        ));
                    }
                }
            }

            return $this->sendSuccess('Data pengajuan lembur berhasil disimpan.');

        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson(
                $e, 
                '[TSU_LEMBUR_STORE_FAIL]', 
                'Gagal menyimpan data pengajuan lembur.', 
                'Lembur Store.'
            );
        }
    }

    public function update(Request $req, $id)
    {
        // $this->guard('edit', 'users:lembur');

        try {
            $validator = Validator::make($req->all(), [
                'id_mlembur' => 'required',
                'tanggal1' => 'required|date',
                'tanggal2' => 'required|date|after_or_equal:tanggal1',
                'alasan' => 'required',
                'id_atasan' => 'required',
                'id_hrd' => 'required',
                'bukti_kegiatan' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ], [
                'bukti_kegiatan.mimes' => 'Format file harus JPG, PNG, atau PDF',
                'bukti_kegiatan.max' => 'Ukuran file maksimal 2MB',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), null, 422);
            }

            $myid = decrypt($id);
            $profile = $this->getCurrentProfile();
            if (!$profile) {
                 return $this->sendError('Profil karyawan tidak ditemukan.', null, 422);
            }

            $dataLembur = [
                'id_mlembur'      => $req->id_mlembur,
                'tanggalmulai'    => $req->tanggal1,
                'tanggalselesai'  => $req->tanggal2,
                'keterangan'      => $req->alasan,
                'id_atasan'       => $req->id_atasan,
                'id_hrd'          => $req->id_hrd,
                'statusatasan'    => 'waiting',
                'statushrd'       => 'waiting',
                'updated_by'      => $profile->nik ?? Auth::id()
            ];

            $lembur = LemburKaryawan::where('id', $myid)
                ->where('is_active', '1')
                ->where('id_user', $profile->id)
                ->first();

            if (!$lembur) {
                throw new \Exception("Data tidak ditemukan");
            }

            if ($lembur->statusatasan != 'draft' || $lembur->statushrd != 'draft') {
                throw new \Exception("Pengajuan hanya bisa disimpan/diedit jika berstatus Draft. Silakan tarik pengajuan terlebih dahulu.");
            }

            if ($req->hasFile('bukti_kegiatan')) {
                if ($lembur->bukti_kegiatan && Storage::disk('public')->exists('lembur/bukti/' . $lembur->bukti_kegiatan)) {
                    Storage::disk('public')->delete('lembur/bukti/' . $lembur->bukti_kegiatan);
                }

                $file = $req->file('bukti_kegiatan');
                $timestamp = date('H-i-s');
                $dateStr = date('d-m-Y');
                $nik = $profile->nik ?? '0000';
                $extension = $file->getClientOriginalExtension();
                $filename = "Lembur_{$dateStr}_{$nik}_{$timestamp}.{$extension}";
                $file->storeAs('public/lembur/bukti', $filename);
                $dataLembur['bukti_kegiatan'] = $filename;
            }

            DB::transaction(function () use ($lembur, $dataLembur) {
                $lembur->update($dataLembur);
            });

            return $this->sendSuccess('Data berhasil disimpan');

        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson(
                $e, 
                '[TSU_LEMBUR_UPD_FAIL]', 
                'Gagal menyimpan perubahan pengajuan lembur.', 
                "Lembur Update ID: $id."
            );
        }
    }

    public function datatable()
    {
        // $this->guard('view', 'users:lembur');

        $profile = $this->getCurrentProfile();
        $profileId = $profile ? $profile->id : null;

        // Using Eager Loading to avoid N+1 issues
        $data = LemburKaryawan::with(['masterLembur', 'atasan', 'hrd'])
            ->where('id_user', $profileId)
            ->where('is_active', '1')
            ->orderByDesc('created_at')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('jenislembur', function ($row) {
                return $row->masterLembur ? $row->masterLembur->jenislembur : '-';
            })
            ->addColumn('waktu', function ($row) {
                $mulai = Carbon::parse($row->tanggalmulai)->format('d M Y H:i');
                $selesai = Carbon::parse($row->tanggalselesai)->format('d M Y H:i');
                return $mulai . ' - ' . $selesai;
            })
            ->addColumn('durasi', function ($row) {
                $start = Carbon::parse($row->tanggalmulai);
                $end   = Carbon::parse($row->tanggalselesai);
                $diffInHours = $start->floatDiffInHours($end);
                return round($diffInHours, 1) . ' Jam';
            })
            ->addColumn('status', function ($row) {
                if ($row->statusatasan == 'draft') return '<span class="badge badge-secondary">Draft</span>';
                if ($row->statusatasan == 'waiting') return '<span class="badge badge-warning">Menunggu Atasan</span>';
                if ($row->statusatasan == 'approved' && $row->statushrd == 'waiting') return '<span class="badge badge-info">Menunggu HRD</span>';
                if ($row->statusatasan == 'approved' && $row->statushrd == 'approved') return '<span class="badge badge-success">Disetujui</span>';
                if ($row->statusatasan == 'rejected' || $row->statushrd == 'rejected') return '<span class="badge badge-danger">Ditolak</span>';
                return '-';
            })
            ->addColumn('nama_atasan', function ($row) {
                return $row->atasan ? $row->atasan->nama : '-';
            })
            ->addColumn('nama_hrd', function ($row) {
                return $row->hrd ? $row->hrd->nama : '-';
            })
            ->addColumn('action', function ($row) {
                $canTarik = ($row->statusatasan == 'waiting' && $row->statushrd == 'waiting');
                $canEdit = ($row->statusatasan == 'draft');
                $canDelete = ($row->statusatasan == 'waiting' || $row->statusatasan == 'draft');

                $btn = $this->getActionButtons($row, 'users:lembur', [
                    'edit_url'   => route('users.lembur.edit', encrypt($row->id)),
                    'delete_url' => route('users.lembur.destroy', encrypt($row->id)),
                    'edit_class' => 'btn-edit',
                    'delete_class' => 'btn-delete',
                    'can_edit'   => $canEdit,
                    'can_delete' => $canDelete,
                ]);

                $detailBtn = '<button type="button" data-url="' . route('users.lembur.show', encrypt($row->id)) . '" class="btn btn-info btn-sm btn-detail ml-1" title="Info Detail"><i class="fas fa-info-circle"></i></button>';
                
                $tarikBtn = '';
                if ($canTarik) {
                    $tarikBtn = '<button type="button" data-url="' . route('users.lembur.tarik', encrypt($row->id)) . '" class="btn btn-warning btn-sm btn-tarik ml-1" title="Tarik Pengajuan"><i class="fas fa-undo"></i></button>';
                }

                return str_replace('</div>', $tarikBtn . $detailBtn . '</div>', $btn);
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function tarik($id)
    {
        try {
            $myid = decrypt($id);
            $profile = $this->getCurrentProfile();

            $lembur = LemburKaryawan::where('id', $myid)
                ->where('id_user', $profile->id)
                ->where('is_active', '1')
                ->first();

            if (!$lembur) {
                throw new \Exception("Data tidak ditemukan");
            }

            if ($lembur->statusatasan != 'waiting' || $lembur->statushrd != 'waiting') {
                throw new \Exception("Tidak bisa menarik pengajuan yang sudah diproses.");
            }

            DB::transaction(function () use ($lembur) {
                $lembur->statusatasan = 'draft';
                $lembur->statushrd = 'draft';
                $lembur->save();
            });

            return $this->sendSuccess('Pengajuan berhasil ditarik dan diubah menjadi Draft.');

        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson(
                $e, 
                '[TSU_LEMBUR_TARIK_FAIL]', 
                'Gagal menarik pengajuan lembur.', 
                "Lembur Tarik ID: $id."
            );
        }
    }

    public function edit($id)
    {
        // $this->guard('edit', 'users:lembur');

        try {
            $myid = decrypt($id);
            $profile = $this->getCurrentProfile();

            $getdata = LemburKaryawan::where('id', $myid)
                ->where('id_user', $profile->id)
                ->where('is_active', '1')
                ->first();
                
            if (!$getdata) {
                throw new \Exception("Data tidak ditemukan");
            }

            if ($getdata->statusatasan != 'draft' || $getdata->statushrd != 'draft') {
                throw new \Exception("Pengajuan hanya bisa diedit jika berstatus Draft.");
            }
                
            $getdata->encrypted_id = $id;
            return $this->sendSuccess('Berhasil memuat data.', $getdata);
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson(
                $e, 
                '[TSU_LEMBUR_EDIT_FAIL]', 
                'Gagal mengambil data pengajuan lembur.', 
                "Lembur Edit Data ID: $id."
            );
        }
    }

    public function show($id)
    {
        // $this->guard('view', 'users:lembur');

        try {
            $myid = decrypt($id);

            // Eager load relationships
            $getdata = LemburKaryawan::with(['masterLembur', 'user', 'atasan', 'hrd'])
                ->where('id', $myid)
                ->where('is_active', '1')
                ->first();
                
            if (!$getdata) {
                throw new \Exception("Data tidak ditemukan");
            }

            $mulai = Carbon::parse($getdata->tanggalmulai);
            $selesai = Carbon::parse($getdata->tanggalselesai);

            $waktu = $mulai->translatedFormat('d M Y H:i') . ' s/d ' . $selesai->translatedFormat('d M Y H:i');
            $durasi = round($mulai->floatDiffInHours($selesai), 1);

            $form = view('users::lembur.modaldetail', [
                'data' => $getdata, 
                'durasi' => $durasi, 
                'waktu' => $waktu
            ]);
            
            return $form->render();
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson(
                $e, 
                '[TSU_LEMBUR_SHOW_FAIL]', 
                'Gagal memuat detail pengajuan lembur.', 
                "Lembur Detail ID: $id."
            );
        }
    }

    public function destroy($id)
    {
        // $this->guard('delete', 'users:lembur');

        try {
            $myid = decrypt($id);
            $profile = $this->getCurrentProfile();

            $lembur = LemburKaryawan::where('id', $myid)
                ->where('id_user', $profile->id)
                ->first();

            if (!$lembur) {
                throw new \Exception("Data tidak ditemukan");
            }

            // Only allow deletion if still waiting or draft
            if (!in_array($lembur->statusatasan, ['waiting', 'draft']) || !in_array($lembur->statushrd, ['waiting', 'draft'])) {
                throw new \Exception("Tidak bisa menghapus pengajuan yang sudah diproses.");
            }

            if ($lembur->bukti_kegiatan && Storage::disk('public')->exists('lembur/bukti/' . $lembur->bukti_kegiatan)) {
                Storage::disk('public')->delete('lembur/bukti/' . $lembur->bukti_kegiatan);
            }

            $lembur->delete();

            return $this->sendSuccess('Data pengajuan berhasil dibatalkan/dihapus');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson(
                $e, 
                '[TSU_LEMBUR_DEL_FAIL]', 
                $e->getMessage() === "Tidak bisa menghapus pengajuan yang sudah diproses." || $e->getMessage() === "Data tidak ditemukan" ? $e->getMessage() : 'Gagal menghapus/membatalkan pengajuan lembur.', 
                "Lembur Delete ID: $id."
            );
        }
    }

    public function datatableApproval()
    {
        // $this->guard('view', 'users:lembur');

        $profile = $this->getCurrentProfile();
        $profileId = $profile ? $profile->id : null;

        $data = LemburKaryawan::with(['masterLembur', 'user'])
            ->where('id_atasan', $profileId)
            ->where('is_active', '1')
            ->orderByDesc('created_at')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('pengaju', function ($row) {
                return $row->user ? $row->user->nama : '-';
            })
            ->addColumn('jenislembur', function ($row) {
                return $row->masterLembur ? $row->masterLembur->jenislembur : '-';
            })
            ->addColumn('waktu', function ($row) {
                $mulai = Carbon::parse($row->tanggalmulai)->format('d M Y H:i');
                $selesai = Carbon::parse($row->tanggalselesai)->format('d M Y H:i');
                return $mulai . ' - ' . $selesai;
            })
            ->addColumn('durasi', function ($row) {
                $start = Carbon::parse($row->tanggalmulai);
                $end   = Carbon::parse($row->tanggalselesai);
                $diffInHours = $start->floatDiffInHours($end);
                return round($diffInHours, 1) . ' Jam';
            })
            ->addColumn('status', function ($row) {
                if ($row->statusatasan == 'waiting') return '<span class="badge badge-warning">Menunggu</span>';
                if ($row->statusatasan == 'approved') return '<span class="badge badge-success">Disetujui</span>';
                if ($row->statusatasan == 'rejected') return '<span class="badge badge-danger">Ditolak</span>';
                return '-';
            })
            ->addColumn('action', function ($row) {
                $detailBtn = '<button type="button" data-url="' . route('users.lembur.show', encrypt($row->id)) . '" class="btn btn-info btn-sm btn-detail" title="Info Detail"><i class="fas fa-info-circle"></i></button>';
                
                $approveBtn = '';
                $rejectBtn = '';

                if ($row->statusatasan == 'waiting') {
                    $approveBtn = '<button type="button" data-id="' . encrypt($row->id) . '" class="btn btn-success btn-sm btn-approve ml-1" title="Setujui"><i class="fas fa-check"></i></button>';
                    $rejectBtn = '<button type="button" data-id="' . encrypt($row->id) . '" class="btn btn-danger btn-sm btn-reject ml-1" title="Tolak"><i class="fas fa-times"></i></button>';
                }

                return '<div class="btn-group" role="group">' . $detailBtn . $approveBtn . $rejectBtn . '</div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function detailPekerjaan(Request $request, $id)
    {
        $this->guard('view', 'users:lembur');
        
        $karyawan = LemburKaryawan::find($id);
        
        if ($karyawan) {
            return response()->json([
                'success' => true,
                'data' => $karyawan->keterangan
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Data tidak ditemukan'
        ], 404);
    }

    private function findAtasanId($unit, $currentUserId)
    {
        if (!$unit) return null;

        $kepalaJabatanId = $unit->kepala_jabatan_id;
        if ($kepalaJabatanId) {
            $kepalas = KaryawanJabatanStruktural::where('jabatan_struktural_id', $kepalaJabatanId)
                ->whereIn('is_active', [1, '1', 'Y', 'y'])
                ->get();
                
            $kepala = null;
            if ($kepalas->count() == 1) {
                $kepala = $kepalas->first();
            } elseif ($kepalas->count() > 1) {
                $kepala = $kepalas->where('unit_id', $unit->id)->first() ?? $kepalas->first();
            }
                
            if ($kepala && $kepala->data_dosen_tendik_id !== $currentUserId) {
                return $kepala->data_dosen_tendik_id;
            }
        }

        if ($unit->parent_unit_id) {
            $parentUnit = \App\Models\MasterUnit::find($unit->parent_unit_id);
            return $this->findAtasanId($parentUnit, $currentUserId);
        }

        return null;
    }

    public function approve(Request $request, $id)
    {
        // $this->guard('edit', 'users:lembur');

        try {
            $myid = decrypt($id);
            $profile = $this->getCurrentProfile();

            $lembur = LemburKaryawan::where('id', $myid)
                ->where('id_atasan', $profile->id)
                ->first();

            if (!$lembur) {
                throw new \Exception("Data tidak ditemukan atau Anda bukan atasan untuk pengajuan ini.");
            }

            if ($lembur->statusatasan != 'waiting') {
                throw new \Exception("Status pengajuan sudah tidak menunggu persetujuan.");
            }

            DB::transaction(function () use ($lembur) {
                $lembur->statusatasan = 'approved';
                $lembur->save();
            });

            return $this->sendSuccess('Pengajuan lembur berhasil disetujui.');

        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson(
                $e, 
                '[TSU_LEMBUR_APP_FAIL]', 
                $e->getMessage() === "Data tidak ditemukan atau Anda bukan atasan untuk pengajuan ini." || $e->getMessage() === "Status pengajuan sudah tidak menunggu persetujuan." ? $e->getMessage() : 'Gagal menyetujui pengajuan lembur.', 
                "Lembur Approve ID: $id."
            );
        }
    }

    public function reject(Request $request, $id)
    {
        // $this->guard('edit', 'users:lembur');

        try {
            $myid = decrypt($id);
            $profile = $this->getCurrentProfile();

            $lembur = LemburKaryawan::where('id', $myid)
                ->where('id_atasan', $profile->id)
                ->first();

            if (!$lembur) {
                throw new \Exception("Data tidak ditemukan atau Anda bukan atasan untuk pengajuan ini.");
            }

            if ($lembur->statusatasan != 'waiting') {
                throw new \Exception("Status pengajuan sudah tidak menunggu persetujuan.");
            }

            DB::transaction(function () use ($lembur) {
                $lembur->statusatasan = 'rejected';
                // Jika atasan menolak, maka hrd juga batal
                $lembur->statushrd = 'rejected';
                $lembur->save();
            });

            return $this->sendSuccess('Pengajuan lembur berhasil ditolak.');

        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson(
                $e, 
                '[TSU_LEMBUR_REJ_FAIL]', 
                $e->getMessage() === "Data tidak ditemukan atau Anda bukan atasan untuk pengajuan ini." || $e->getMessage() === "Status pengajuan sudah tidak menunggu persetujuan." ? $e->getMessage() : 'Gagal menolak pengajuan lembur.', 
                "Lembur Reject ID: $id."
            );
        }
    }
}
