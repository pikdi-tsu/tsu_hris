<?php

namespace Modules\Admin\Http\Controllers\EmployeeLifecycle;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\MasterUnit;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PhkController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        // Admin HR Access
    }

    /**
     * Halaman Utama Pemutusan Hubungan Kerja (PHK)
     */
    public function index(Request $request)
    {
        $totalNonAktif = DataDosenTendik::where('is_active', 0)->count();
        $totalAktif = DataDosenTendik::where('is_active', 1)->count();
        $totalDosenPhk = DataDosenTendik::where('is_active', 0)->where('tipe_karyawan', 'Dosen')->count();
        $totalTendikPhk = DataDosenTendik::where('is_active', 0)->where('tipe_karyawan', 'Tendik')->count();

        $units = MasterUnit::orderBy('nama_unit')->get();

        return view('admin::employee-lifecycle.phk.index', [
            'title'          => 'Monitoring Pemutusan Hubungan Kerja (PHK)',
            'menu'           => 'phk',
            'menuIcon'       => 'fas fa-user-slash',
            'totalNonAktif'  => $totalNonAktif,
            'totalAktif'     => $totalAktif,
            'totalDosenPhk'  => $totalDosenPhk,
            'totalTendikPhk' => $totalTendikPhk,
            'units'          => $units,
        ]);
    }

    /**
     * Datatable Karyawan PHK / Non-Aktif
     */
    public function datatable(Request $request)
    {
        $query = DataDosenTendik::with('unit')
            ->where('is_active', 0)
            ->select('data_dosen_tendiks.*');

        if ($request->filled('tipe_karyawan')) {
            $query->where('tipe_karyawan', $request->tipe_karyawan);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pegawai_info', function ($row) {
                $gelarDepan = $row->gelar_depan ? $row->gelar_depan . ' ' : '';
                $gelarBelakang = $row->gelar_belakang ? ', ' . $row->gelar_belakang : '';
                $namaLengkap = $gelarDepan . $row->nama . $gelarBelakang;
                $nik = $row->nik ?: ($row->nuptk ? 'NUPTK: ' . $row->nuptk : '-');

                return '<div><div class="font-weight-bold text-dark" style="font-size: 0.9rem;">' . e($namaLengkap) . '</div><div class="small text-muted mt-0.5">NIK: ' . e($nik) . '</div></div>';
            })
            ->addColumn('tipe_badge', function ($row) {
                return $row->tipe_karyawan === 'Dosen'
                    ? '<span class="tsu-badge-soft tsu-badge-dosen">Dosen</span>'
                    : '<span class="tsu-badge-soft tsu-badge-tendik">Tendik</span>';
            })
            ->addColumn('unit_info', function ($row) {
                return $row->unit ? '<span class="font-weight-semibold text-dark">' . e($row->unit->nama_unit) . '</span>' : '<span class="text-muted">-</span>';
            })
            ->addColumn('posisi_info', function ($row) {
                return $row->posisi ? e($row->posisi) : '<span class="text-muted">-</span>';
            })
            ->addColumn('status_badge', function ($row) {
                return '<span class="tsu-badge-soft tsu-urgensi-kritis">Non-Aktif / PHK</span>';
            })
            ->addColumn('aksi', function ($row) {
                return '<div class="btn-group">' .
                    '<button type="button" class="btn btn-sm btn-outline-info btn-detail-pegawai" data-id="' . $row->id . '" title="Lihat Detail Profil"><i class="fas fa-eye mr-1"></i> Detail</button>' .
                    '<a href="' . route('admin.pelaksanaan-onboarding-offboarding.index') . '#tab-offboarding" class="btn btn-sm btn-outline-warning" title="Buka Checklist Offboarding"><i class="fas fa-clipboard-check mr-1"></i> Offboarding</a>' .
                '</div>';
            })
            ->rawColumns(['pegawai_info', 'tipe_badge', 'unit_info', 'posisi_info', 'status_badge', 'aksi'])
            ->make(true);
    }

    /**
     * Detail Karyawan Non-Aktif / PHK (JSON for Modal)
     */
    public function detail($id)
    {
        $pegawai = DataDosenTendik::with(['unit', 'statusKaryawan', 'user'])->findOrFail($id);

        $gelarDepan = $pegawai->gelar_depan ? $pegawai->gelar_depan . ' ' : '';
        $gelarBelakang = $pegawai->gelar_belakang ? ', ' . $pegawai->gelar_belakang : '';
        $namaLengkap = $gelarDepan . $pegawai->nama . $gelarBelakang;

        return response()->json([
            'id'                  => $pegawai->id,
            'nama_lengkap'        => $namaLengkap,
            'nik'                 => $pegawai->nik ?: '-',
            'nidn'                => $pegawai->nidn ?: ($pegawai->nuptk ?: '-'),
            'tipe_karyawan'       => $pegawai->tipe_karyawan ?: 'Tendik',
            'posisi'              => $pegawai->posisi ?: '-',
            'unit_kerja'          => $pegawai->unit ? $pegawai->unit->nama_unit : '-',
            'status_kerja'        => $pegawai->statusKaryawan ? $pegawai->statusKaryawan->nama_status : 'Kontrak / PKWT',
            'pendidikan_terakhir' => $pegawai->pendidikan_terakhir ?: '-',
            'tgl_bergabung'       => $pegawai->tgl_bergabung ? Carbon::parse($pegawai->tgl_bergabung)->translatedFormat('d F Y') : '-',
            'no_hp'               => $pegawai->no_hp ?: '-',
            'email'               => $pegawai->user ? $pegawai->user->email : '-',
            'alamat_lengkap'      => $pegawai->alamat_lengkap ?: '-',
            'status_aktif'        => 'Non-Aktif / PHK',
        ]);
    }

    /**
     * Modal Form Proses PHK / Purna Tugas
     */
    public function modalAdd()
    {
        $pegawais = DataDosenTendik::with('unit')
            ->where('is_active', 1)
            ->orderBy('nama')
            ->get();

        return view('admin::employee-lifecycle.phk.modal_add', compact('pegawais'));
    }

    /**
     * Simpan Pemutusan Hubungan Kerja (PHK)
     */
    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id'          => 'required|string|exists:data_dosen_tendiks,id',
            'tgl_efektif'         => 'required|date',
            'alasan_phk'          => 'required|string',
            'no_sk_phk'           => 'nullable|string|max:100',
            'keterangan'          => 'nullable|string',
            'trigger_offboarding' => 'nullable',
        ]);

        try {
            DB::beginTransaction();

            $pegawai = DataDosenTendik::findOrFail($request->pegawai_id);

            // 1. Nonaktifkan status kepegawaian
            $pegawai->is_active = 0;
            $pegawai->save();

            // 2. Putus kontrak aktif jika ada
            \App\Models\PegawaiKontrak::where('pegawai_id', $pegawai->id)
                ->where('status', 'aktif')
                ->update([
                    'status'     => 'diputus',
                    'keterangan' => DB::raw("CONCAT(COALESCE(keterangan, ''), ' | PHK per " . $request->tgl_efektif . ": " . addslashes($request->alasan_phk) . "')")
                ]);

            // 3. Trigger Alur Offboarding Otomatis
            if ($request->boolean('trigger_offboarding', true)) {
                $tipe = strtolower($pegawai->tipe_karyawan ?? 'tendik');
                $masterTasks = \App\Models\MasterOnboardingOffboarding::active()
                    ->offboarding()
                    ->where(function($q) use ($tipe) {
                        $q->where('sasaran', 'semua')
                          ->orWhere('sasaran', $tipe);
                    })
                    ->get();

                foreach ($masterTasks as $task) {
                    \App\Models\KaryawanOnboardingOffboarding::firstOrCreate(
                        [
                            'data_dosen_tendik_id'            => $pegawai->id,
                            'master_onboarding_offboarding_id' => $task->id,
                        ],
                        [
                            'is_completed' => false,
                            'catatan'      => 'Diinisiasi otomatis via Modul PHK (' . $request->alasan_phk . ')',
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Status PHK berhasil diproses dan pegawai telah dinonaktifkan.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses PHK: ' . $e->getMessage(),
            ], 500);
        }
    }
}
