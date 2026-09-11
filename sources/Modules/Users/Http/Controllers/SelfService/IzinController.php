<?php

namespace Modules\Users\Http\Controllers\SelfService;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;
use Modules\System\Models\MenuSidebar;

use App\Models\IzinKaryawan;
use App\Models\MasterIzin;
use App\Models\SaldoCutiKaryawan;
use App\Models\DataDosenTendik;
use App\Models\KaryawanJabatanStruktural;
use App\Traits\ApiResponseTrait;
use App\Services\TsuErrorHandlerService;
use App\Models\User;

class IzinController extends Controller
{
    use ApiResponseTrait;
    public function __construct()
    {
        //        $this->middleware('checklogin');
        $this->middleware('auth');
        //        $this->middleware('verified');
    }

    private function getCurrentProfile()
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $profile = DataDosenTendik::where('user_id', $user->id)->first();

        // Fallback: Jika belum tertaut user_id, cari berdasarkan nama atau NIK
        if (!$profile) {
            $profile = DataDosenTendik::where('nama', $user->name)
                ->orWhere('nik', $user->name)
                ->first();

            if ($profile && empty($profile->user_id)) {
                $profile->update(['user_id' => $user->id]);
            }
        }

        return $profile;
    }

    public function index()
    {

        if (Session::has('tmp')) {
            Session::forget('tmp');
        }

        $getmizin = MasterIzin::where('is_active', '1')->get();
        $profile = $this->getCurrentProfile();

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
        $atasanId = null;
        if ($profile) {
            $isKepala = KaryawanJabatanStruktural::where('data_dosen_tendik_id', $profile->id)
                ->whereIn('is_active', [1, '1', 'Y', 'y'])->exists();

            if ($profile->unit_id) {
                $unit = \App\Models\MasterUnit::find($profile->unit_id);
                if ($unit) {
                    $atasanId = $this->findAtasanId($unit, $profile->id);
                    if ($atasanId) {
                        $atasan = DataDosenTendik::find($atasanId);
                        if ($atasan) {
                            $namaAtasan = $atasan->nama;
                        }
                    }
                }
            }
        }

        $getsaldo = $profile ? SaldoCutiKaryawan::where('id_user', $profile->id)->where('is_active', '1')->first() : null;

        $menuData = MenuSidebar::where('route', 'users.izin.index')->first();
        $data = array(
            'title'     => 'Izin Karyawan',
            'menu'      => 'dashboard',
            'mizin'     => $getmizin,
            'karyawans' => $listSdm,
            'profile'   => $profile,
            'saldo'     => $getsaldo,
            'namaAtasan' => $namaAtasan,
            'menuIcon'  => $menuData->icon ?? 'fas fa-id-badge',
        );

        return view('users::izin.index', $data);
    }

    public function simpan(Request $req)
    {
        try {
            $rules = [
                'jenisizin'  => 'required',
                'tanggal1'   => 'required|date',
                'tanggal2'   => 'required|date|after_or_equal:tanggal1',
                'alasan'     => 'required',
                'id_hrd'     => 'required',
                'file_bukti' => ($req->ketedit == 'no')
                    ? 'required|file|mimes:pdf,jpg,jpeg,png|max:10240'
                    : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            ];

            $messages = [
                'jenisizin.required'      => 'Jenis Izin Tidak Boleh Kosong',
                'tanggal1.required'       => 'Tanggal Mulai Tidak Boleh Kosong',
                'tanggal2.required'       => 'Tanggal Selesai Tidak Boleh Kosong',
                'tanggal2.after_or_equal' => 'Waktu Selesai harus setelah Waktu Mulai',
                'alasan.required'         => 'Alasan Tidak Boleh Kosong',
                'id_hrd.required'         => 'HRD Tidak Boleh Kosong',
                'file_bukti.required'     => 'Berkas Bukti Dukungan wajib diunggah untuk pengajuan Izin.',
                'file_bukti.mimes'        => 'Format berkas bukti izin harus berupa PDF, JPG, JPEG, atau PNG.',
                'file_bukti.max'          => 'Ukuran berkas bukti izin maksimal 10 MB.',
            ];

            $validator = Validator::make($req->all(), $rules, $messages);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first());
            }

            $profile = $this->getCurrentProfile();
            if (!$profile) {
                return $this->sendError('Profil karyawan tidak ditemukan.');
            }

            if (!$profile->unit_id) {
                return $this->sendError('Unit Anda tidak ditemukan di sistem.');
            }

            $unit = \App\Models\MasterUnit::find($profile->unit_id);
            if (!$unit) {
                return $this->sendError('Unit Anda tidak ditemukan di sistem.');
            }

            $idatasan = $this->findAtasanId($unit, $profile->id);
            if (!$idatasan) {
                return $this->sendError('Unit Anda (atau Unit Induk) belum memiliki Kepala Unit. Silakan hubungi SDM.');
            }

            $iduser = $profile->id;
            $jenisizin = $req->jenisizin;
            $tgl1 = $req->tanggal1;
            $tgl2 = $req->tanggal2;
            $alasan = $req->alasan;
            $idhrd = $req->id_hrd;

            // Handle upload file bukti izin (Private Storage)
            $filePath = null;
            if ($req->hasFile('file_bukti')) {
                $file = $req->file('file_bukti');
                $filename = 'BuktiIzin_' . ($profile->nik ?? 'user') . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('private/izin/bukti', $filename);
                $filePath = 'private/izin/bukti/' . $filename;
            }

            $jumlahHari = IzinKaryawan::hitungHariEfektif($tgl1, $tgl2);
            if ($jumlahHari <= 0) {
                return $this->sendError('Gagal mengajukan: Rentang tanggal yang dipilih tidak memuat hari kerja efektif (semua tanggal merupakan akhir pekan atau hari libur nasional).');
            }

            $izinId = null;
            if ($req->ketedit == 'no') {
                $insertData = [
                    'id_mizin'        => $jenisizin,
                    'id_user'         => $iduser,
                    'tanggalmulai'    => $tgl1,
                    'tanggalselesai'  => $tgl2,
                    'tanggaldiajukan' => date("Y-m-d H:i:s"),
                    'keterangan'      => $alasan,
                    'file_bukti'      => $filePath,
                    'id_atasan'       => $idatasan,
                    'statusatasan'    => 'waiting',
                    'id_hrd'          => $idhrd,
                    'statushrd'       => 'waiting',
                    'created_at'      => date("Y-m-d H:i:s"),
                    'created_by'      => $profile->nik ?? Auth::id()
                ];
                $izinId = IzinKaryawan::insertGetId($insertData);
            } else {
                $izinId = $req->idedit;
                $updateData = [
                    'id_mizin'        => $jenisizin,
                    'id_user'         => $iduser,
                    'tanggalmulai'    => $tgl1,
                    'tanggalselesai'  => $tgl2,
                    'tanggaldiajukan' => date("Y-m-d H:i:s"),
                    'keterangan'      => $alasan,
                    'id_atasan'       => $idatasan,
                    'statusatasan'    => 'waiting',
                    'id_hrd'          => $idhrd,
                    'statushrd'       => 'waiting',
                    'updated_at'      => date("Y-m-d H:i:s"),
                    'updated_by'      => $profile->nik ?? Auth::id()
                ];
                if ($filePath) {
                    $updateData['file_bukti'] = $filePath;
                }
                IzinKaryawan::where('id', $izinId)->where('is_active', '1')->update($updateData);
            }

            // Real-Time Notifications
            if ($izinId) {
                $izinCreated = IzinKaryawan::find($izinId);

                // Notify Atasan
                if ($idatasan) {
                    $atasanProfile = DataDosenTendik::find($idatasan);
                    if ($atasanProfile && $atasanProfile->user_id) {
                        $atasanUser = User::find($atasanProfile->user_id);
                        if ($atasanUser) {
                            $atasanUser->notify(new \App\Notifications\IzinDiajukanNotification(
                                $izinCreated,
                                'Pengajuan izin baru dari ' . ($profile->nama ?? 'Bawahan') . ' menunggu persetujuan Anda.'
                            ));
                        }
                    }
                }

                // Notify HRD
                if ($idhrd) {
                    $hrdProfile = DataDosenTendik::find($idhrd);
                    if ($hrdProfile && $hrdProfile->user_id) {
                        $hrdUser = User::find($hrdProfile->user_id);
                        if ($hrdUser) {
                            $hrdUser->notify(new \App\Notifications\IzinDiajukanNotification(
                                $izinCreated,
                                'Ada pengajuan izin baru dari ' . ($profile->nama ?? 'Karyawan') . ' yang diajukan ke Atasan.',
                                'hrd'
                            ));
                        }
                    }
                }
            }

            return $this->sendSuccess('Izin Berhasil Disimpan');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_SS_IZIN_SAVE_FAIL]', 'Gagal menyimpan data izin.');
        }
    }

    public function datatables()
    {
        $profile = $this->getCurrentProfile();
        $profileId = $profile ? $profile->id : null;

        $data = IzinKaryawan::with(['masterIzin', 'atasan', 'hrd'])
            ->where('id_user', $profileId)
            ->where('is_active', '1')
            ->orderByDesc('created_at')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('jenisizin', function ($data) {
                return $data->masterIzin ? $data->masterIzin->jenisizin : '-';
            })
            ->addColumn('tanggalmulai', function ($data) {
                $formatTanggal = Carbon::parse($data->tanggalmulai)->format('d F Y');
                return $formatTanggal;
            })
            ->addColumn('tanggalselesai', function ($data) {
                $formatTanggal = Carbon::parse($data->tanggalselesai)->format('d F Y');
                return $formatTanggal;
            })
            ->addColumn('jumlah', function ($data) {
                return IzinKaryawan::hitungHariEfektif($data->tanggalmulai, $data->tanggalselesai);
            })
            ->addColumn('statusatasan', function ($data) {
                if ($data->statusatasan == 'approved') {
                    $stat = '<span style="display:inline-flex;align-items:center;gap:.25rem;padding:.22rem .65rem;border-radius:20px;font-size:.72rem;font-weight:600;background:#dcfce7;color:#166534;"><i class="fas fa-check-circle" style="font-size:.62rem;"></i> Disetujui</span>';
                } elseif ($data->statusatasan == 'rejected') {
                    $stat = '<span style="display:inline-flex;align-items:center;gap:.25rem;padding:.22rem .65rem;border-radius:20px;font-size:.72rem;font-weight:600;background:#fee2e2;color:#991b1b;"><i class="fas fa-times-circle" style="font-size:.62rem;"></i> Ditolak</span>';
                } else {
                    $stat = '<span style="display:inline-flex;align-items:center;gap:.25rem;padding:.22rem .65rem;border-radius:20px;font-size:.72rem;font-weight:600;background:#fef9c3;color:#854d0e;"><i class="fas fa-hourglass-half" style="font-size:.62rem;"></i> Menunggu</span>';
                }
                return $stat;
            })
            ->addColumn('statushrd', function ($data) {
                if ($data->statushrd == 'approved') {
                    $stat = '<span style="display:inline-flex;align-items:center;gap:.25rem;padding:.22rem .65rem;border-radius:20px;font-size:.72rem;font-weight:600;background:#dcfce7;color:#166534;"><i class="fas fa-check-circle" style="font-size:.62rem;"></i> Disetujui</span>';
                } elseif ($data->statushrd == 'rejected') {
                    $stat = '<span style="display:inline-flex;align-items:center;gap:.25rem;padding:.22rem .65rem;border-radius:20px;font-size:.72rem;font-weight:600;background:#fee2e2;color:#991b1b;"><i class="fas fa-times-circle" style="font-size:.62rem;"></i> Ditolak</span>';
                } else {
                    $stat = '<span style="display:inline-flex;align-items:center;gap:.25rem;padding:.22rem .65rem;border-radius:20px;font-size:.72rem;font-weight:600;background:#fef9c3;color:#854d0e;"><i class="fas fa-hourglass-half" style="font-size:.62rem;"></i> Menunggu</span>';
                }
                return $stat;
            })
            ->addColumn('file_bukti', function ($data) {
                if ($data->file_bukti && $data->file_bukti_url) {
                    return '<a href="' . $data->file_bukti_url . '" target="_blank" download class="btn btn-xs btn-outline-info rounded-pill px-2" title="Unduh / Lihat Bukti"><i class="fas fa-paperclip mr-1"></i> Bukti</a>';
                }
                return '<span class="text-muted small">-</span>';
            })
            ->addColumn('action', function ($data) {
                $encId = encrypt($data->id);
                if ($data->statusatasan != 'waiting' || $data->statushrd != 'waiting') {
                    $button = '<div style="display:flex;justify-content:center;">'
                        . '<a href="#" data-id="' . $encId . '" id="btndetail" title="Lihat Detail" '
                        . 'style="width:30px;height:30px;border-radius:8px;background:#f0fdf4;color:#166534;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;transition:all .15s;">'
                        . '<i class="fas fa-eye"></i></a>'
                        . '</div>';
                } else {
                    $button = '<div style="display:flex;justify-content:center;">'
                        . '<a href="#" data-id="' . $encId . '" id="btnedit" title="Edit Pengajuan" '
                        . 'style="width:30px;height:30px;border-radius:8px;background:#e0f2fe;color:#0369a1;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;transition:all .15s;">'
                        . '<i class="fas fa-edit"></i></a>'
                        . '</div>';
                }
                return $button;
            })
            ->rawColumns(['statusatasan', 'statushrd', 'file_bukti', 'action'])
            ->make(true);
    }

    public function edit(Request $req)
    {
        try {
            $myid = decrypt($req->myid);

            $getdata = IzinKaryawan::where('id', $myid)
                ->where('is_active', '1')
                ->first();

            return response()->json($getdata, 200);
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_SS_IZIN_EDIT_FAIL]', 'Gagal memuat data izin.');
        }
    }

    public function detail(Request $req)
    {
        try {
            $myid = decrypt($req->myid);

            $profile = $this->getCurrentProfile();

            $getdata = IzinKaryawan::with(['masterIzin', 'atasan', 'hrd'])
                ->where('id', $myid)
                ->where('is_active', '1')
                ->orderByDesc('created_at')
                ->first();
            // dd($getdata);
            $mulai = Carbon::parse($getdata->tanggalmulai);
            $selesai = Carbon::parse($getdata->tanggalselesai);

            if ($mulai->format('Y-m') == $selesai->format('Y-m')) {
                // bulan & tahun sama
                $tanggal = $mulai->translatedFormat('d') . '–' . $selesai->translatedFormat('d M Y');
            } else {
                // bulan atau tahun beda
                $tanggal = $mulai->translatedFormat('d M Y') . ' - ' . $selesai->translatedFormat('d M Y');
            }

            $jumlahHari = IzinKaryawan::hitungHariEfektif($getdata->tanggalmulai, $getdata->tanggalselesai);

            $form = view('users::izin.modaldetail', ['data' => $getdata, 'profile' => $profile, 'jmlhari' => $jumlahHari, 'tanggal' => $tanggal]);
            return $form->render();
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_SS_IZIN_DTL]', 'Gagal memuat detail izin.');
        }
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

    /**
     * Stream atau unduh berkas bukti izin secara privat
     */
    public function streamBukti($id)
    {
        $izin = IzinKaryawan::with('user')->findOrFail($id);

        $currentUser = Auth::user();
        $currentProfile = $this->getCurrentProfile();

        $canAccess = false;
        if ($currentUser && ($currentUser->isAdmin() || $currentUser->hasRole(['superadmin', 'admin', 'hrd']))) {
            $canAccess = true;
        } elseif ($currentProfile) {
            if ($izin->id_user == $currentProfile->id || $izin->id_atasan == $currentProfile->id || $izin->id_hrd == $currentProfile->id) {
                $canAccess = true;
            }
        }

        if (!$canAccess) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat berkas bukti izin ini.');
        }

        $filePath = $izin->file_bukti;
        if (!$filePath) {
            abort(404, 'Berkas bukti izin tidak ditemukan.');
        }

        $candidates = [
            storage_path('app/' . ltrim($filePath, '/')),
            storage_path('app/public/' . str_replace('storage/', '', ltrim($filePath, '/'))),
            public_path(ltrim($filePath, '/')),
            public_path('storage/' . str_replace('storage/', '', ltrim($filePath, '/'))),
        ];

        $fullPath = null;
        foreach ($candidates as $cand) {
            if (file_exists($cand) && is_file($cand)) {
                $fullPath = $cand;
                break;
            }
        }

        if (!$fullPath) {
            abort(404, 'File fisik bukti izin tidak ditemukan di server.');
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
        $filename = 'Bukti_Izin_' . ($izin->user->nama ?? 'Pegawai') . '.' . pathinfo($fullPath, PATHINFO_EXTENSION);

        return response()->file($fullPath, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
