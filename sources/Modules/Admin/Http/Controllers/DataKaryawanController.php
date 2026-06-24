<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use App\Services\HomebaseSyncService;
use App\Models\MasterJabatanStruktural;
use App\Models\MasterJabatanFungsional;
use App\Models\RiwayatJabatan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\KaryawanJabatanStruktural;
use App\Models\KaryawanJabatanFungsional;
use App\Models\MasterPangkatGolongan;

use App\Traits\ApiResponseTrait;

class DataKaryawanController extends MiddlewareController
{
    use ApiResponseTrait;
    
    public function __construct()
    {
        $this->registerPermissions('admin:data-karyawan');
    }

    /**
     * Menampilkan halaman utama Data Karyawan
     */
    public function index()
    {
        return view('admin::data-karyawan.index', ['title' => 'Data Dosen & Tendik']);
    }

    /**
     * Sumber data JSON untuk Yajra DataTables
     */
    public function datatable()
    {
        $data = DataDosenTendik::with([
            'unit',
            'statusKaryawan',
            'jabatanFungsionals' => function($q){
                $q->where('is_active', 'Y')->with('masterFungsional');
            },
            'jabatanStrukturals' => function($q){
                $q->where('is_active', 'Y')->with('masterStruktural');
            }
        ])->orderBy('nama', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nama_lengkap', function($row) {
                // Nama beserta gelar
                $gelarDepan = $row->gelar_depan ? $row->gelar_depan . ' ' : '';
                $gelarBelakang = $row->gelar_belakang ? ', ' . $row->gelar_belakang : '';
                $namaLengkap = $gelarDepan . $row->nama . $gelarBelakang;

                // No.HP
                $kontak = '';
                if (!empty($row->no_hp)) {
                    $href = str_starts_with($row->no_hp, 'wa.me/') ? 'https://' . $row->no_hp : $row->no_hp;
                    $teksTampil = str_replace('wa.me/', '', $row->no_hp);

                    $kontak = '<div class="mt-2">
                       <a href="' . $href . '" target="_blank" class="text-success font-weight-bold shadow-sm"
                          style="background-color: #e8f5e9; padding: 4px 10px; border-radius: 20px; text-decoration: none; font-size: 0.75rem; border: 1px solid #c8e6c9; display: inline-block;">
                           <i class="fab fa-whatsapp mr-1" style="font-size: 0.9rem;"></i> Chat WA
                       </a>
                   </div>';
                }

                return '<div class="font-weight-bold text-dark text-uppercase" style="line-height: 1.2;">' . $namaLengkap . '</div>' . $kontak;
            })
            ->addColumn('identitas', function($row) {
                // Badge NIK & NIDN
                $nik = '<span class="badge badge-secondary mb-1">NIK: ' . $row->nik . '</span><br>';
                $warnaNidn = $row->nidn ? 'badge-success' : 'badge-warning';
                $teksNidn = $row->nidn ?? 'Tidak Ada';
                $nidn = '<span class="badge ' . $warnaNidn . '">NIDN: ' . $teksNidn . '</span>';

                return $nik . $nidn;
            })
            ->addColumn('jabatan', function ($row) {
                // LOGIC JABATAN STRUKTURAL (Bisa Lebih dari 1)
                if ($row->jabatanStrukturals->isEmpty()) {
                    $htmlStruktural = '
                        <div class="d-flex align-items-center mb-1 text-muted">
                            <div class="bg-light rounded-circle mr-2 d-flex justify-content-center align-items-center border shadow-sm" style="width: 24px; height: 24px; min-width: 24px;">
                                <i class="fas fa-minus text-muted" style="font-size: 0.65rem;"></i>
                            </div>
                            <span class="font-italic" style="font-size: 0.85rem;">Tidak ada struktural</span>
                        </div>';
                } else {
                    $badgesStr = '';
                    foreach($row->jabatanStrukturals as $js) {
                        $namaStr = $js->masterStruktural ? $js->masterStruktural->nama_jabatan : 'Unknown';
                        $badgesStr .= '
                        <div class="d-flex align-items-center mb-1">
                            <div class="bg-dark rounded-circle mr-2 d-flex justify-content-center align-items-center shadow-sm" style="width: 24px; height: 24px; min-width: 24px;">
                                <i class="fas fa-briefcase text-white" style="font-size: 0.65rem;"></i>
                            </div>
                            <span class="font-weight-bold text-dark" style="font-size: 0.9rem; line-height: 1.2;">'.$namaStr.'</span>
                        </div>';
                    }
                    $htmlStruktural = $badgesStr;
                }

                // LOGIC JABATAN FUNGSIONAL (Bisa Lebih dari 1)
                if ($row->jabatanFungsionals->isEmpty()) {
                    $htmlFungsional = '
                        <div class="d-flex align-items-center mt-2 text-muted">
                            <div class="bg-light rounded-circle mr-2 d-flex justify-content-center align-items-center border shadow-sm" style="width: 24px; height: 24px; min-width: 24px;">
                                <i class="fas fa-minus text-muted" style="font-size: 0.65rem;"></i>
                            </div>
                            <span class="font-italic" style="font-size: 0.85rem;">Tidak ada fungsional</span>
                        </div>';
                } else {
                    $badges = '';
                    foreach($row->jabatanFungsionals as $jf) {
                        $namaFung = $jf->masterFungsional ? $jf->masterFungsional->nama_jabatan : 'Unknown';
                        $badges .= '
                        <div class="d-flex align-items-center mt-2">
                            <div class="bg-info rounded-circle mr-2 d-flex justify-content-center align-items-center shadow-sm" style="width: 24px; height: 24px; min-width: 24px;">
                                <i class="fas fa-medal text-white" style="font-size: 0.65rem;"></i>
                            </div>
                            <span class="font-weight-bold text-info" style="font-size: 0.85rem; line-height: 1.2;">'.$namaFung.'</span>
                        </div>';
                    }
                    $htmlFungsional = $badges;
                }

                return $htmlStruktural . $htmlFungsional;
            })
            ->addColumn('homebase_posisi', function($row) {
                // Tipe Karyawan Badge
                $tipeKaryawanBadge = '';
                if ($row->tipe_karyawan == 'Dosen') {
                    $tipeKaryawanBadge = '<span class="badge badge-primary px-2 py-1 mb-1 shadow-sm"><i class="fas fa-chalkboard-teacher mr-1"></i> DOSEN</span>';
                } elseif ($row->tipe_karyawan == 'Tendik') {
                    $tipeKaryawanBadge = '<span class="badge badge-warning text-dark px-2 py-1 mb-1 shadow-sm"><i class="fas fa-user-tie mr-1"></i> TENDIK</span>';
                } else {
                    $tipeKaryawanBadge = '<span class="badge badge-secondary px-2 py-1 mb-1 shadow-sm">Belum Diatur</span>';
                }

                // Homebase / Unit
                $unitName = $row->unit ? $row->unit->nama_unit : '<span class="text-black-50 font-italic">Homebase belum diatur</span>';
                $htmlUnit = '<div class="font-weight-bold text-dark mt-1" style="font-size: 0.9rem;"><i class="fas fa-building text-info mr-1"></i> ' . $unitName . '</div>';

                // Posisi
                $posisi = $row->posisi ? $row->posisi : '<span class="text-black-50 font-italic">Posisi belum diatur</span>';
                $htmlPosisi = '<div class="text-muted mt-1" style="font-size: 0.85rem;"><i class="fas fa-user-tag text-secondary mr-1"></i> ' . $posisi . '</div>';

                return $tipeKaryawanBadge . $htmlUnit . $htmlPosisi;
            })
            ->addColumn('status_karyawan', function($row) {
                $statusBadge = ($row->is_active == 1) 
                    ? '<span class="badge badge-success mb-1">AKTIF</span>' 
                    : '<span class="badge badge-danger mb-1">NON-AKTIF</span>';
                
                $tipeBadge = '<span class="badge badge-info">' . ($row->statusKaryawan ? $row->statusKaryawan->nama_status : 'Belum Diatur') . '</span>';
                
                return $statusBadge . '<br>' . $tipeBadge;
            })
            ->addColumn('aksi', function ($row) {
                $showUrl = route('admin.data-karyawan.show', $row->id);
                $editUrl = route('admin.data-karyawan.edit', $row->id);
                $kelolaStrukturalUrl = route('admin.data-karyawan.kelola-struktural', $row->id);
                $kelolaFungsionalUrl = route('admin.data-karyawan.kelola-fungsional', $row->id);
                $mutasiUrl = route('admin.data-karyawan.mutasi', $row->id);
                $riwayatUrl = route('admin.data-karyawan.riwayat', $row->id);
                $deleteUrl = route('admin.data-karyawan.destroy', $row->id);
                $token = csrf_token();

                $btnDetail = '<button type="button" class="btn btn-sm btn-info text-white mx-1 btn-modal" data-url="'.$showUrl.'" title="Detail Profil"><i class="fas fa-eye"></i></button>';
                $btnEdit = '<button type="button" class="btn btn-sm btn-warning btn-edit text-dark mx-1" data-url="'.$editUrl.'" title="Edit Profil"><i class="fas fa-pencil-alt"></i></button>';
                $btnMutasi = '<button type="button" class="btn btn-sm btn-primary text-white mx-1 btn-modal" data-url="'.$mutasiUrl.'" title="Pindah Jabatan (Mutasi)"><i class="fas fa-exchange-alt"></i></button>';
                $btnRiwayat = '<button type="button" class="btn btn-sm btn-secondary text-white mx-1 btn-modal" data-url="'.$riwayatUrl.'" title="Riwayat Jabatan"><i class="fas fa-history"></i></button>';

                if ($row->is_active == 0) {
                    // JIKA NON-AKTIF: Tombol HIJAU (Nembak ke route POST 'aktifkan')
                    $aktifkanUrl = route('admin.data-karyawan.bio-aktif', $row->id);
                    $btnToggle = '
                        <form action="'.$aktifkanUrl.'" method="POST" style="display:inline-block; margin: 0;" class="mx-1">
                            <input type="hidden" name="_token" value="'.$token.'">
                            <button type="button" class="btn btn-sm btn-success btn-toggle-status" data-action="aktifkan" data-name="'. htmlspecialchars($row->nama) .'" title="Aktifkan Karyawan">
                                <i class="fas fa-user-check"></i>
                            </button>
                        </form>
                    ';
                } else {
                    // JIKA AKTIF: Tombol MERAH (Nembak ke route DELETE 'destroy')
                    $deleteUrl = route('admin.data-karyawan.destroy', $row->id);
                    $btnToggle = '
                        <form action="'.$deleteUrl.'" method="POST" style="display:inline-block; margin: 0;" class="mx-1">
                            <input type="hidden" name="_token" value="'.$token.'">
                            <input type="hidden" name="_method" value="DELETE"> <button type="button" class="btn btn-sm btn-danger btn-toggle-status" data-action="nonaktifkan" data-name="'. htmlspecialchars($row->nama) .'" title="Nonaktifkan Karyawan">
                                <i class="fas fa-user-slash"></i>
                            </button>
                        </form>
                    ';
                }

                return '<div class="d-flex justify-content-center align-items-center">' . $btnDetail . $btnEdit . $btnMutasi . $btnRiwayat . $btnToggle . '</div>';
            })
            ->filterColumn('jabatan', function($query, $keyword) {
                $query->whereHas('jabatanStrukturals.masterStruktural', function($q) use ($keyword) {
                    $q->where('nama_jabatan', 'like', "%{$keyword}%");
                })
                ->orWhereHas('jabatanFungsionals.masterFungsional', function($q) use ($keyword) {
                    $q->where('nama_jabatan', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('homebase_posisi', function($query, $keyword) {
                $query->whereHas('unit', function($q) use ($keyword) {
                    $q->where('nama_unit', 'like', "%{$keyword}%");
                })
                ->orWhere('posisi', 'like', "%{$keyword}%")
                ->orWhere('tipe_karyawan', 'like', "%{$keyword}%");
            })
            ->filterColumn('status_karyawan', function($query, $keyword) {
                $keywordLower = strtolower(trim($keyword));
                if (in_array($keywordLower, ['aktif', 'akt', 'akti'])) {
                    $query->where('is_active', 1)
                          ->orWhereHas('statusKaryawan', function($q) use ($keyword) {
                              $q->where('nama_status', 'like', "%{$keyword}%");
                          });
                } elseif (in_array($keywordLower, ['nonaktif', 'non-aktif', 'non', 'non aktif'])) {
                    $query->where('is_active', 0)
                          ->orWhereHas('statusKaryawan', function($q) use ($keyword) {
                              $q->where('nama_status', 'like', "%{$keyword}%");
                          });
                } else {
                    $query->whereHas('statusKaryawan', function($q) use ($keyword) {
                        $q->where('nama_status', 'like', "%{$keyword}%");
                    });
                }
            })
            ->rawColumns(['nama_lengkap', 'identitas', 'homebase_posisi', 'jabatan', 'status_karyawan', 'aksi'])
            ->make(true);
    }

    /**
     * Menampilkan Modal Detail Karyawan (Read Only)
     */
    public function show($id)
    {
        $karyawan = DataDosenTendik::with([
            'jabatanStrukturals' => function($q) { $q->where('is_active', 'Y')->with('masterStruktural'); },
            'jabatanFungsionals' => function($q) { $q->where('is_active', 'Y')->with(['masterFungsional', 'pangkatGolongan']); },
        ])->findOrFail($id);
        $formConfig = DataDosenTendik::getFormConfig();

        return view('admin::data-karyawan.show_modal', compact('karyawan', 'formConfig'));
    }

    /**
     * Menampilkan Modal Form Tambah
     */
    public function create()
    {
        $this->guard('create', 'admin:data-karyawan');

        $formConfig = DataDosenTendik::getFormConfig();
        
        // Hapus tab jabatan pada saat tambah data baru
        unset($formConfig['tab_kepangkatan']);

        // Pastikan file view ini nanti dibuat ya Bosku
        return view('admin::data-karyawan.create_modal', compact('formConfig'));
    }

    /**
     * Menyimpan data dari Modal Form
     */
    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:data-karyawan');

        // Validasi sesuaikan dengan kebutuhan field-mu
        $request->validate([
            'nik'  => 'required|unique:data_dosen_tendiks,nik',
            'nama' => 'required|string|max:255',
        ]);

        try {
            $data = $request->all();

            if (!empty($data['no_hp'])) {
                $cleanPhone = str_replace(['+', ' '], '', trim($data['no_hp']));

                if (!str_starts_with($cleanPhone, 'wa.me/')) {
                    $data['no_hp'] = 'wa.me/' . $cleanPhone;
                }
            }

            $newKaryawan = DataDosenTendik::create($data);

            return back()->with('new_karyawan_id', $newKaryawan->id);
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e, 
                '[TSU_KARYAWAN_STORE_FAIL]', 
                'Gagal menyimpan data karyawan baru.', 
                'Gagal Create Karyawan.', 
                $request
            );
        }
    }

    /**
     * Menampilkan Modal Form Edit
     */
    public function edit($id)
    {
        $this->guard('edit', 'admin:data-karyawan');

        $formConfig = DataDosenTendik::getFormConfig();
        $karyawan = DataDosenTendik::findOrFail($id);

        return view('admin::data-karyawan.edit_modal', compact('karyawan', 'formConfig'));
    }

    /**
     * Menyimpan update data dari Modal
     */
    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:data-karyawan');

        $karyawan = DataDosenTendik::findOrFail($id);

        $request->validate([
            'nik'  => 'required|unique:data_dosen_tendiks,nik,' . $id,
            'nama' => 'required|string|max:255',
        ]);

        try {
            $data = $request->except(['id', 'user_id']);

            if (!empty($data['no_hp'])) {
                $cleanPhone = str_replace(['+', ' '], '', trim($data['no_hp']));
                if (!str_starts_with($cleanPhone, 'wa.me/')) {
                    $data['no_hp'] = 'wa.me/' . $cleanPhone;
                }
            }

            $karyawan->update($data);

            return back()->with('success', 'Data karyawan berhasil diperbarui!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e, 
                '[TSU_KARYAWAN_UPD_FAIL]', 
                'Gagal menyimpan perubahan data karyawan.', 
                "Gagal Update Karyawan ID: $id.", 
                $request
            );
        }
    }

    /**
     * Menghapus Data Karyawan (Nonaktifkan)
     */
    public function destroy($id)
    {
        $this->guard('delete', 'admin:karyawan');
        $karyawan = DataDosenTendik::with('user')->findOrFail($id);

        try {
            DB::beginTransaction();
            
            // Sync dengan Homebase jika user tertaut
            $ssoId = $karyawan->user->sso_id ?? null;
            if ($ssoId) {
                HomebaseSyncService::syncUserStatus($ssoId, false);
            }

            $karyawan->update([
                'is_active' => 0
                // user_id sengaja TIDAK di-null-kan agar relasi ke Homebase tetap terjaga
            ]);

            DB::commit();
            return back()->with('success', 'Data karyawan berhasil dinonaktifkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleHtml(
                $e, 
                '[TSU_KARYAWAN_DEL_FAIL]', 
                'Gagal menonaktifkan data karyawan karena gangguan sinkronisasi.', 
                "Gagal Nonaktifkan ID: $id."
            );
        }
    }

    /**
     * Mengaktifkan Kembali Data Karyawan
     */
    public function bioAktif($id)
    {
        $this->guard('edit', 'admin:data-karyawan');
        $karyawan = DataDosenTendik::with('user')->findOrFail($id);

        try {
            DB::beginTransaction();

            // Sync dengan Homebase jika user tertaut
            $ssoId = $karyawan->user->sso_id ?? null;
            if ($ssoId) {
                HomebaseSyncService::syncUserStatus($ssoId, true);
            }

            $karyawan->update([
                'is_active' => 1
            ]);

            DB::commit();
            return back()->with('success', 'Mantap! Data karyawan berhasil diaktifkan kembali.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleHtml(
                $e, 
                '[TSU_KARYAWAN_ACT_FAIL]', 
                'Gagal mengaktifkan kembali data karyawan karena gangguan sinkronisasi.', 
                "Gagal Aktifkan ID: $id."
            );
        }
    }

    /**
     * Menampilkan Modal Form Mutasi Jabatan
     */
    public function mutasiModal($id)
    {
        $this->guard('edit', 'admin:data-karyawan');
        
        $karyawan = DataDosenTendik::with([
            'jabatanStrukturals' => function($q) {
                $q->where('is_active', 'Y')->with('masterStruktural');
            },
            'jabatanFungsionals' => function($q) {
                $q->where('is_active', 'Y')->with('masterFungsional');
            }
        ])->findOrFail($id);
        
        $listKaryawan = DataDosenTendik::where('is_active', 1)
            ->where('id', '!=', $id)
            ->orderBy('nama', 'asc')
            ->get();
            
        $listStruktural = MasterJabatanStruktural::orderBy('nama_jabatan', 'asc')->get();
        $listFungsional = MasterJabatanFungsional::orderBy('nama_jabatan', 'asc')->get();

        return view('admin::data-karyawan.mutasi_modal', compact(
            'karyawan', 'listKaryawan', 'listStruktural', 'listFungsional'
        ));
    }

    /**
     * Menyimpan data Mutasi Jabatan
     */
    public function storeMutasi(Request $request, $id)
    {
        $this->guard('edit', 'admin:data-karyawan');
        
        $request->validate([
            'tipe_jabatan' => 'required|in:struktural,fungsional',
            'opsi_pengganti' => 'nullable|in:lanjutkan,periode_baru',
            'keterangan' => 'nullable|string'
        ]);

        $pegawaiLama = DataDosenTendik::findOrFail($id);
        
        DB::beginTransaction();
        try {
            $tipe = $request->tipe_jabatan;
            $tglSekarang = Carbon::now();
            
            // Riwayat data array
            $riwayatData = [
                'data_dosen_tendik_id' => $pegawaiLama->id,
                'tipe_jabatan' => $tipe,
                'keterangan' => $request->keterangan, // bisa null (opsional)
                'tgl_selesai' => $tglSekarang->format('Y-m-d')
            ];

            if ($tipe == 'struktural') {
                $strukturalLamaId = $request->karyawan_jabatan_struktural_id;
                if (!$strukturalLamaId) {
                    throw new \Exception("Pilih jabatan struktural yang akan dimutasi.");
                }
                
                $jabatanStrPivot = KaryawanJabatanStruktural::find($strukturalLamaId);
                if (!$jabatanStrPivot || $jabatanStrPivot->data_dosen_tendik_id != $pegawaiLama->id) {
                    throw new \Exception("Data jabatan struktural tidak valid.");
                }

                $tglMulai = Carbon::parse($jabatanStrPivot->tgl_mulai);
                $lamaBulan = $tglMulai->diffInMonths($tglSekarang);
                
                $riwayatData['jabatan_struktural_id'] = $jabatanStrPivot->jabatan_struktural_id;
                $riwayatData['unit_id'] = $jabatanStrPivot->unit_id;
                $riwayatData['tgl_mulai'] = $jabatanStrPivot->tgl_mulai;
                $riwayatData['lama_menjabat_bulan'] = $lamaBulan;
                
                // Nonaktifkan jabatan lama
                $jabatanStrPivot->update([
                    'is_active' => 'N',
                    'tgl_akhir' => $tglSekarang->format('Y-m-d')
                ]);
                
                $jabBaruLama = $request->jabatan_baru_pegawai_lama ?: null;
                if ($jabBaruLama) {
                    KaryawanJabatanStruktural::create([
                        'data_dosen_tendik_id' => $pegawaiLama->id,
                        'jabatan_struktural_id' => $jabBaruLama,
                        'tgl_mulai' => $tglSekarang->format('Y-m-d'),
                        'is_active' => 'Y'
                    ]);
                }
            } else {
                $fungsionalLamaId = $request->karyawan_jabatan_fungsional_id;
                if (!$fungsionalLamaId) {
                    throw new \Exception("Pilih jabatan fungsional yang akan dimutasi.");
                }
                
                $jabatanFungPivot = KaryawanJabatanFungsional::find($fungsionalLamaId);
                if (!$jabatanFungPivot || $jabatanFungPivot->data_dosen_tendik_id != $pegawaiLama->id) {
                    throw new \Exception("Data jabatan fungsional tidak valid.");
                }

                $tglMulai = Carbon::parse($jabatanFungPivot->tgl_mulai);
                $lamaBulan = $tglMulai->diffInMonths($tglSekarang);
                
                $riwayatData['jabatan_fungsional_id'] = $jabatanFungPivot->jabatan_fungsional_id;
                $riwayatData['tgl_mulai'] = $jabatanFungPivot->tgl_mulai;
                $riwayatData['lama_menjabat_bulan'] = $lamaBulan;
                
                // Nonaktifkan jabatan lama
                $jabatanFungPivot->update([
                    'is_active' => 'N',
                    'tgl_akhir' => $tglSekarang->format('Y-m-d')
                ]);
                
                // Jika diberi jabatan baru
                $jabBaruLamaId = $request->jabatan_baru_pegawai_lama ?: null;
                if ($jabBaruLamaId) {
                    KaryawanJabatanFungsional::create([
                        'data_dosen_tendik_id' => $pegawaiLama->id,
                        'jabatan_fungsional_id' => $jabBaruLamaId,
                        'tgl_mulai' => $tglSekarang->format('Y-m-d'),
                        'sk_jabatan' => null,
                        'is_active' => 'Y'
                    ]);
                }
            }

            RiwayatJabatan::create($riwayatData);

            if ($request->pegawai_pengganti_id) {
                $pegawaiBaru = DataDosenTendik::findOrFail($request->pegawai_pengganti_id);
                
                if ($tipe == 'struktural') {
                    $master = MasterJabatanStruktural::find($riwayatData['jabatan_struktural_id']);
                    $lamaMasterBulan = $master->periode_jabatan ?? 0;
                    
                    if ($request->opsi_pengganti == 'lanjutkan') {
                        $sisaBulan = max(0, $lamaMasterBulan - $lamaBulan);
                        $tglAkhir = $tglSekarang->copy()->addMonths($sisaBulan);
                    } else {
                        $tglAkhir = $tglSekarang->copy()->addMonths($lamaMasterBulan);
                    }
                    
                    KaryawanJabatanStruktural::create([
                        'data_dosen_tendik_id' => $pegawaiBaru->id,
                        'jabatan_struktural_id' => $riwayatData['jabatan_struktural_id'],
                        'unit_id' => $riwayatData['unit_id'],
                        'tgl_mulai' => $tglSekarang->format('Y-m-d'),
                        'is_active' => 'Y'
                    ]);
                } else {
                    $master = MasterJabatanFungsional::find($riwayatData['jabatan_fungsional_id']);
                    $lamaMasterBulan = $master->periode_jabatan ?? 0;
                    
                    if ($request->opsi_pengganti == 'lanjutkan') {
                        $sisaBulan = max(0, $lamaMasterBulan - $lamaBulan);
                        $tglAkhir = $tglSekarang->copy()->addMonths($sisaBulan);
                    } else {
                        $tglAkhir = $tglSekarang->copy()->addMonths($lamaMasterBulan);
                    }
                    
                    KaryawanJabatanFungsional::create([
                        'data_dosen_tendik_id' => $pegawaiBaru->id,
                        'jabatan_fungsional_id' => $riwayatData['jabatan_fungsional_id'],
                        'tgl_mulai' => $tglSekarang->format('Y-m-d'),
                        'sk_jabatan' => 'Mutasi dari ' . $pegawaiLama->nama,
                        'is_active' => 'Y'
                    ]);
                }
            }

            DB::commit();
            return $this->sendSuccess('Berhasil memproses mutasi jabatan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e, 
                '[TSU_MUTASI_FAIL]', 
                'Gagal memproses mutasi jabatan: ' . $e->getMessage(), 
                "Gagal Mutasi ID: $id."
            );
        }
    }

    /**
     * KELOLA JABATAN FUNGSIONAL
     */
    public function kelolaFungsionalModal($id)
    {
        $this->guard('edit', 'admin:data-karyawan');
        $karyawan = DataDosenTendik::findOrFail($id);
        
        $fungsionals = KaryawanJabatanFungsional::where('data_dosen_tendik_id', $id)
            ->where('is_active', 'Y')
            ->with('masterFungsional')
            ->orderBy('tgl_mulai', 'desc')
            ->get();
            
        $masterFungsional = MasterJabatanFungsional::orderBy('nama_jabatan', 'asc')->get();
        $masterPangkat = MasterPangkatGolongan::orderBy('nama_pangkat_golongan', 'asc')->get();

        return view('admin::data-karyawan.kelola_fungsional_modal', compact('karyawan', 'fungsionals', 'masterFungsional', 'masterPangkat'));
    }

    public function storeFungsional(Request $request, $id)
    {
        $this->guard('edit', 'admin:data-karyawan');
        $request->validate([
            'jabatan_fungsional_id' => 'required',
            'pangkat_golongan_id' => 'nullable',
            'tgl_mulai' => 'required|date',
            'sk_jabatan' => 'nullable|string|max:255',
        ]);

        try {
            $master = MasterJabatanFungsional::find($request->jabatan_fungsional_id);
            $tglAkhir = null;
            if ($master && $master->periode_jabatan && $request->tgl_mulai) {
                $tglAkhir = Carbon::parse($request->tgl_mulai)->addMonths($master->periode_jabatan)->format('Y-m-d');
            }

            KaryawanJabatanFungsional::create([
                'data_dosen_tendik_id' => $id,
                'jabatan_fungsional_id' => $request->jabatan_fungsional_id,
                'pangkat_golongan_id' => $request->pangkat_golongan_id ?: null,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_akhir' => $tglAkhir,
                'sk_jabatan' => $request->sk_jabatan,
                'is_active' => 'Y'
            ]);
            
            // Return JSON for AJAX modal refresh
            return $this->sendSuccess('Jabatan fungsional berhasil ditambahkan.', [
                'html' => view('admin::data-karyawan._fungsional_list', [
                    'fungsionals' => KaryawanJabatanFungsional::where('data_dosen_tendik_id', $id)->where('is_active', 'Y')->with(['masterFungsional', 'pangkatGolongan'])->orderBy('tgl_mulai', 'desc')->get()
                ])->render()
            ]);
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_FUNG_STORE]', 'Gagal menambah fungsional.', 'Gagal Store Fungsional');
        }
    }

    public function destroyFungsional($fungsional_id)
    {
        $this->guard('edit', 'admin:data-karyawan');
        try {
            $fung = KaryawanJabatanFungsional::findOrFail($fungsional_id);
            $karyawanId = $fung->data_dosen_tendik_id;
            
            // Soft delete/deactivate instead of hard delete
            $fung->update([
                'is_active' => 'N',
                'tgl_akhir' => Carbon::now()->format('Y-m-d')
            ]);
            
            return $this->sendSuccess('Jabatan fungsional berhasil dihapus/dinonaktifkan.', [
                'html' => view('admin::data-karyawan._fungsional_list', [
                    'fungsionals' => KaryawanJabatanFungsional::where('data_dosen_tendik_id', $karyawanId)->where('is_active', 'Y')->with('masterFungsional')->orderBy('tgl_mulai', 'desc')->get()
                ])->render()
            ]);
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_FUNG_DEL]', 'Gagal menghapus fungsional.', 'Gagal Delete Fungsional');
        }
    }

    /**
     * KELOLA JABATAN STRUKTURAL
     */
    public function kelolaStrukturalModal($id)
    {
        $this->guard('edit', 'admin:data-karyawan');
        $karyawan = DataDosenTendik::findOrFail($id);
        
        $strukturals = KaryawanJabatanStruktural::where('data_dosen_tendik_id', $id)
            ->where('is_active', 'Y')
            ->with(['masterStruktural', 'unit'])
            ->orderBy('tgl_mulai', 'desc')
            ->get();
            
        $masterStruktural = MasterJabatanStruktural::orderBy('nama_jabatan', 'asc')->get();
        $masterUnit = \App\Models\MasterUnit::orderBy('nama_unit', 'asc')->get();

        return view('admin::data-karyawan.kelola_struktural_modal', compact('karyawan', 'strukturals', 'masterStruktural', 'masterUnit'));
    }

    public function storeStruktural(Request $request, $id)
    {
        $this->guard('edit', 'admin:data-karyawan');
        $request->validate([
            'jabatan_struktural_id' => 'required',
            'tgl_mulai' => 'required|date',
            'unit_id' => 'nullable'
        ]);

        try {
            $master = MasterJabatanStruktural::find($request->jabatan_struktural_id);
            if ($master && $master->is_unit_specific == 'Y' && empty($request->unit_id)) {
                throw new \Exception("Jabatan ini mewajibkan Unit Penugasan untuk diisi.");
            }

            $tglAkhir = null;
            if ($master && $master->periode_jabatan && $request->tgl_mulai) {
                $tglAkhir = Carbon::parse($request->tgl_mulai)->addMonths($master->periode_jabatan)->format('Y-m-d');
            }

            KaryawanJabatanStruktural::create([
                'data_dosen_tendik_id' => $id,
                'jabatan_struktural_id' => $request->jabatan_struktural_id,
                'unit_id' => $master->is_unit_specific == 'Y' ? $request->unit_id : null,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_akhir' => $tglAkhir,
                'is_active' => 'Y'
            ]);
            
            return $this->sendSuccess('Jabatan struktural berhasil ditambahkan.', [
                'html' => view('admin::data-karyawan._struktural_list', [
                    'strukturals' => KaryawanJabatanStruktural::where('data_dosen_tendik_id', $id)->where('is_active', 'Y')->with(['masterStruktural', 'unit'])->orderBy('tgl_mulai', 'desc')->get()
                ])->render()
            ]);
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_STR_STORE]', 'Gagal menambah struktural.', 'Gagal Store Struktural');
        }
    }

    public function destroyStruktural($struktural_id)
    {
        $this->guard('edit', 'admin:data-karyawan');
        try {
            $str = KaryawanJabatanStruktural::findOrFail($struktural_id);
            $karyawanId = $str->data_dosen_tendik_id;
            
            $str->update([
                'is_active' => 'N',
                'tgl_akhir' => Carbon::now()->format('Y-m-d')
            ]);
            
            return $this->sendSuccess('Jabatan struktural berhasil dilepas.', [
                'html' => view('admin::data-karyawan._struktural_list', [
                    'strukturals' => KaryawanJabatanStruktural::where('data_dosen_tendik_id', $karyawanId)->where('is_active', 'Y')->with(['masterStruktural', 'unit'])->orderBy('tgl_mulai', 'desc')->get()
                ])->render()
            ]);
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleJson($e, '[TSU_STR_DEL]', 'Gagal melepas struktural.', 'Gagal Delete Struktural');
        }
    }
    // =========================================================================
    // MODUL RIWAYAT JABATAN
    // =========================================================================

    public function riwayatModal($id)
    {
        $this->guard('view', 'admin:data-karyawan');
        $karyawan = DataDosenTendik::findOrFail($id);
        
        $riwayats = RiwayatJabatan::with(['jabatanStruktural', 'jabatanFungsional', 'pangkatGolongan', 'unit'])
            ->where('data_dosen_tendik_id', $id)
            ->orderBy('tgl_selesai', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('admin::data-karyawan.riwayat_modal', compact('karyawan', 'riwayats'));
    }
    public function exportRiwayatExcel($id)
    {
        $this->guard('view', 'admin:data-karyawan');
        
        $count = RiwayatJabatan::where('data_dosen_tendik_id', $id)->count();
        if ($count === 0) {
            return response('<script>alert("Gagal: Pegawai ini belum memiliki catatan riwayat jabatan untuk diekspor!"); window.close();</script>');
        }

        $karyawan = DataDosenTendik::findOrFail($id);
        
        $fileName = 'Riwayat_Jabatan_' . str_replace(' ', '_', $karyawan->nama) . '_' . date('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RiwayatJabatanExport($id), $fileName);
    }
}
