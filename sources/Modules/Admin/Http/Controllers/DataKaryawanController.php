<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use App\Models\MasterJabatanStruktural;
use App\Models\MasterJabatanFungsional;
use App\Models\RiwayatJabatan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DataKaryawanController extends MiddlewareController
{
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
        // Tarik data mentah
        $data = DataDosenTendik::query()->orderBy('nama', 'asc');

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
                // LOGIC JABATAN STRUKTURAL
                if (empty($row->jabatan_struktural)) {
                    $htmlStruktural = '<div class="text-muted font-italic small mb-1">Tidak ada jabatan struktural</div>';
                } else {
                    $htmlStruktural = '<div class="font-weight-bold text-dark">' . $row->jabatan_struktural . '</div>';
                }

                // LOGIC JABATAN FUNGSIONAL
                if (empty($row->jabatan_fungsional)) {
                    $htmlFungsional = '<span class="text-muted small"><i class="fas fa-layer-group mr-1"></i>Fungsional: <span class="font-italic">-</span></span>';
                } else {
                    $htmlFungsional = '<span class="text-secondary small"><i class="fas fa-layer-group mr-1"></i>Fungsional: ' . $row->jabatan_fungsional . '</span>';
                }

                return $htmlStruktural . $htmlFungsional;
            })
            ->addColumn('status_karyawan', function($row) {
                if ($row->status_karyawan === 'NON-AKTIF') {
                    return '<span class="badge badge-danger">' . $row->status_karyawan . '</span>';
                }
                return '<span class="badge badge-success">' . ($row->status_karyawan) . '</span>';
            })
            ->addColumn('aksi', function ($row) {
                $showUrl = route('admin.data-karyawan.show', $row->id);
                $editUrl = route('admin.data-karyawan.edit', $row->id);
                $mutasiUrl = route('admin.data-karyawan.mutasi', $row->id);
                $deleteUrl = route('admin.data-karyawan.destroy', $row->id);
                $token = csrf_token();

                $btnDetail = '<button type="button" class="btn btn-sm btn-info text-white mx-1 btn-modal" data-url="'.$showUrl.'" title="Detail Profil"><i class="fas fa-eye"></i></button>';
                $btnEdit = '<button type="button" class="btn btn-sm btn-warning btn-edit text-dark mx-1" data-url="'.$editUrl.'" title="Edit Profil"><i class="fas fa-pencil-alt"></i></button>';
                $btnMutasi = '<button type="button" class="btn btn-sm btn-primary text-white mx-1 btn-modal" data-url="'.$mutasiUrl.'" title="Pindah Jabatan (Mutasi)"><i class="fas fa-exchange-alt"></i></button>';

                if ($row->status_karyawan === 'NON-AKTIF') {
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

                return '<div class="d-flex justify-content-center align-items-center">' . $btnDetail . $btnEdit . $btnMutasi . $btnToggle . '</div>';
            })
            ->rawColumns(['nama_lengkap', 'identitas', 'jabatan', 'status_karyawan', 'aksi'])
            ->make(true);
    }

    /**
     * Menampilkan Modal Detail Karyawan (Read Only)
     */
    public function show($id)
    {
        $karyawan = DataDosenTendik::findOrFail($id);
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

            DataDosenTendik::create($request->all());

            return back()->with('success', 'Data karyawan berhasil ditambahkan!');
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
            $data = $request->except(['id', 'user_id', 'status_karyawan']);

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
        $karyawan = DataDosenTendik::findOrFail($id);

        try {
            $karyawan->update([
                'user_id' => null,
                'status_karyawan' => 'NON-AKTIF'
            ]);
            return back()->with('success', 'Data karyawan berhasil dinonaktifkan.');
        } catch (\Exception $e) {
            Log::error("[TSU_KARYAWAN_DEL_FAIL] Gagal Nonaktifkan ID: $id. Error: " . $e->getMessage());
            return back()->with('error', 'Gagal menonaktifkan data karyawan.');
        }
    }

    /**
     * Mengaktifkan Kembali Data Karyawan
     */
    public function bioAktif($id)
    {
        $this->guard('edit', 'admin:data-karyawan');
        $karyawan = DataDosenTendik::findOrFail($id);

        try {
            $karyawan->update([
                'status_karyawan' => 'AKTIF'
            ]);
            return back()->with('success', 'Mantap! Data karyawan berhasil diaktifkan kembali.');
        } catch (\Exception $e) {
            Log::error("[TSU_KARYAWAN_ACT_FAIL] Gagal Aktifkan ID: $id. Error: " . $e->getMessage());
            return back()->with('error', 'Gagal mengaktifkan kembali data karyawan.');
        }
    }

    /**
     * Menampilkan Modal Form Mutasi Jabatan
     */
    public function mutasiModal($id)
    {
        $this->guard('edit', 'admin:data-karyawan');
        
        $karyawan = DataDosenTendik::with(['jabatanStruktural', 'jabatanFungsional'])->findOrFail($id);
        
        $listKaryawan = DataDosenTendik::where('status_karyawan', 'AKTIF')
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
                if (!$pegawaiLama->jabatan_struktural_id) {
                    throw new \Exception("Pegawai ini tidak memiliki jabatan struktural.");
                }
                
                $tglMulai = Carbon::parse($pegawaiLama->tgl_mulai_jabatan_struktural);
                $lamaBulan = $tglMulai->diffInMonths($tglSekarang);
                
                $riwayatData['jabatan_struktural_id'] = $pegawaiLama->jabatan_struktural_id;
                $riwayatData['tgl_mulai'] = $pegawaiLama->tgl_mulai_jabatan_struktural;
                $riwayatData['lama_menjabat_bulan'] = $lamaBulan;
                
                $jabBaruLama = $request->jabatan_baru_pegawai_lama ?: null;
                $pegawaiLama->update([
                    'jabatan_struktural_id' => $jabBaruLama,
                    'tgl_mulai_jabatan_struktural' => $jabBaruLama ? $tglSekarang->format('Y-m-d') : null,
                    'tgl_akhir_jabatan_struktural' => null
                ]);
            } else {
                if (!$pegawaiLama->jabatan_fungsional_id) {
                    throw new \Exception("Pegawai ini tidak memiliki jabatan fungsional.");
                }
                
                $tglMulai = Carbon::parse($pegawaiLama->tgl_mulai_jabatan_fungsional);
                $lamaBulan = $tglMulai->diffInMonths($tglSekarang);
                
                $riwayatData['jabatan_fungsional_id'] = $pegawaiLama->jabatan_fungsional_id;
                $riwayatData['tgl_mulai'] = $pegawaiLama->tgl_mulai_jabatan_fungsional;
                $riwayatData['lama_menjabat_bulan'] = $lamaBulan;
                
                $jabBaruLama = $request->jabatan_baru_pegawai_lama ?: null;
                $pegawaiLama->update([
                    'jabatan_fungsional_id' => $jabBaruLama,
                    'tgl_mulai_jabatan_fungsional' => $jabBaruLama ? $tglSekarang->format('Y-m-d') : null,
                    'tgl_akhir_jabatan_fungsional' => null
                ]);
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
                    
                    $pegawaiBaru->update([
                        'jabatan_struktural_id' => $riwayatData['jabatan_struktural_id'],
                        'tgl_mulai_jabatan_struktural' => $tglSekarang->format('Y-m-d'),
                        'tgl_akhir_jabatan_struktural' => $tglAkhir->format('Y-m-d')
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
                    
                    $pegawaiBaru->update([
                        'jabatan_fungsional_id' => $riwayatData['jabatan_fungsional_id'],
                        'tgl_mulai_jabatan_fungsional' => $tglSekarang->format('Y-m-d'),
                        'tgl_akhir_jabatan_fungsional' => $tglAkhir->format('Y-m-d')
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Berhasil memproses mutasi jabatan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleHtml(
                $e, 
                '[TSU_MUTASI_FAIL]', 
                'Gagal memproses mutasi jabatan: ' . $e->getMessage(), 
                "Gagal Mutasi ID: $id.", 
                $request
            );
        }
    }
}
