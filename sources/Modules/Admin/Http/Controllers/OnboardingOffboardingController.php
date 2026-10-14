<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\DataDosenTendik;
use App\Models\MasterOnboardingOffboarding;
use App\Models\KaryawanOnboardingOffboarding;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class OnboardingOffboardingController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:data-karyawan');
    }

    /**
     * Helper konfigurasi tugas master onboarding (dosen vs tendik)
     */
    private function getOnboardingTaskConfig(): array
    {
        $dosenIds = MasterOnboardingOffboarding::active()->onboarding()->whereIn('sasaran', ['semua', 'dosen'])->pluck('id')->toArray();
        $tendikIds = MasterOnboardingOffboarding::active()->onboarding()->whereIn('sasaran', ['semua', 'tendik'])->pluck('id')->toArray();

        $dosenIn = !empty($dosenIds) ? implode(',', $dosenIds) : '0';
        $tendikIn = !empty($tendikIds) ? implode(',', $tendikIds) : '0';
        $countDosen = count($dosenIds);
        $countTendik = count($tendikIds);

        $completedSql = "
            (SELECT COUNT(*) FROM karyawan_onboarding_offboardings
             WHERE karyawan_onboarding_offboardings.data_dosen_tendik_id = data_dosen_tendiks.id
               AND karyawan_onboarding_offboardings.is_completed = 1
               AND (
                   (LOWER(data_dosen_tendiks.tipe_karyawan) = 'dosen' AND karyawan_onboarding_offboardings.master_onboarding_offboarding_id IN ({$dosenIn}))
                   OR
                   (LOWER(data_dosen_tendiks.tipe_karyawan) != 'dosen' AND karyawan_onboarding_offboardings.master_onboarding_offboarding_id IN ({$tendikIn}))
               ))
        ";

        $requiredSql = "
            (CASE 
                WHEN LOWER(data_dosen_tendiks.tipe_karyawan) = 'dosen' THEN {$countDosen}
                ELSE {$countTendik}
            END)
        ";

        return [
            'dosenIds'     => $dosenIds,
            'tendikIds'    => $tendikIds,
            'completedSql' => $completedSql,
            'requiredSql'  => $requiredSql,
        ];
    }

    /**
     * Helper konfigurasi tugas master offboarding
     */
    private function getOffboardingTaskConfig(): array
    {
        $offboardIds = MasterOnboardingOffboarding::active()->offboarding()->pluck('id')->toArray();
        $offboardIn = !empty($offboardIds) ? implode(',', $offboardIds) : '0';
        $countOffboard = count($offboardIds);

        $completedSql = "
            (SELECT COUNT(*) FROM karyawan_onboarding_offboardings
             WHERE karyawan_onboarding_offboardings.data_dosen_tendik_id = data_dosen_tendiks.id
               AND karyawan_onboarding_offboardings.is_completed = 1
               AND karyawan_onboarding_offboardings.master_onboarding_offboarding_id IN ({$offboardIn}))
        ";

        $requiredSql = "{$countOffboard}";

        return [
            'offboardIds'  => $offboardIds,
            'completedSql' => $completedSql,
            'requiredSql'  => $requiredSql,
        ];
    }

    /**
     * Hitung statistik jumlah pegawai di masing-masing tab (real-time)
     */
    private function calculateStats(): array
    {
        $onb = $this->getOnboardingTaskConfig();
        $off = $this->getOffboardingTaskConfig();

        // 1. Onboarding Berjalan (Pegawai baru / sedang proses checklist yang belum 100%)
        $sixMonthsAgo = now()->subMonths(6);
        $onboardingBerjalanCount = DataDosenTendik::where('is_active', 1)
            ->where(function($q) use ($sixMonthsAgo) {
                $q->where('tgl_bergabung', '>=', $sixMonthsAgo)
                  ->orWhereNull('tgl_bergabung')
                  ->orWhereExists(function($sub) {
                      $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                          ->from('karyawan_onboarding_offboardings')
                          ->whereColumn('karyawan_onboarding_offboardings.data_dosen_tendik_id', 'data_dosen_tendiks.id');
                  });
            })
            ->whereRaw("{$onb['completedSql']} < {$onb['requiredSql']}")
            ->count();

        // 2. Riwayat Onboarding (Checklist sudah lengkap 100%)
        $riwayatOnboardingCount = DataDosenTendik::where('is_active', 1)
            ->whereRaw("{$onb['completedSql']} >= {$onb['requiredSql']} AND {$onb['completedSql']} > 0")
            ->count();

        // 3. Offboarding Berjalan (Pegawai nonaktif/resign yang serah terimanya belum 100%)
        $offboardingBerjalanCount = DataDosenTendik::where('is_active', 0)
            ->whereRaw("{$off['completedSql']} < {$off['requiredSql']}")
            ->count();

        // 4. Riwayat Offboarding (Pegawai nonaktif/resign yang serah terimanya sudah 100%)
        $riwayatOffboardingCount = DataDosenTendik::where('is_active', 0)
            ->whereRaw("{$off['completedSql']} >= {$off['requiredSql']} AND {$off['completedSql']} > 0")
            ->count();

        return [
            'onboarding_berjalan'  => $onboardingBerjalanCount,
            'riwayat_onboarding'   => $riwayatOnboardingCount,
            'offboarding_berjalan' => $offboardingBerjalanCount,
            'riwayat_offboarding'  => $riwayatOffboardingCount,
        ];
    }

    /**
     * Endpoint AJAX JSON untuk real-time update counter angka di tab & stat cards
     */
    public function getStatsJson()
    {
        return response()->json($this->calculateStats());
    }

    /**
     * Dashboard Pemantauan Onboarding & Offboarding Pegawai
     */
    public function index()
    {
        $stats = $this->calculateStats();
        $totalTasks = MasterOnboardingOffboarding::where('is_active', 1)->count();

        return view('admin::onboarding-offboarding.index', [
            'title'                    => 'Employee Transition (Onboarding & Offboarding)',
            'menuIcon'                 => 'fas fa-user-check',
            'onboardingBerjalanCount'  => $stats['onboarding_berjalan'],
            'riwayatOnboardingCount'   => $stats['riwayat_onboarding'],
            'offboardingBerjalanCount' => $stats['offboarding_berjalan'],
            'riwayatOffboardingCount'  => $stats['riwayat_offboarding'],
            'totalTasks'               => $totalTasks,
        ]);
    }

    /**
     * DataTables Daftar Pegawai untuk Onboarding (Sedang Berjalan / Belum Lengkap)
     */
    public function datatableOnboarding(Request $request)
    {
        $onb = $this->getOnboardingTaskConfig();
        $sixMonthsAgo = now()->subMonths(6);

        $query = DataDosenTendik::with(['unit'])
            ->where('is_active', 1)
            ->where(function($q) use ($sixMonthsAgo) {
                $q->where('tgl_bergabung', '>=', $sixMonthsAgo)
                  ->orWhereNull('tgl_bergabung')
                  ->orWhereExists(function($sub) {
                      $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                          ->from('karyawan_onboarding_offboardings')
                          ->whereColumn('karyawan_onboarding_offboardings.data_dosen_tendik_id', 'data_dosen_tendiks.id');
                  });
            })
            ->whereRaw("{$onb['completedSql']} < {$onb['requiredSql']}");

        if ($request->filled('tipe_karyawan') && in_array(strtolower($request->tipe_karyawan), ['dosen', 'tendik'])) {
            $query->where('tipe_karyawan', $request->tipe_karyawan);
        }

        $query->orderBy('tgl_bergabung', 'desc')->orderBy('id', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('identitas', function ($row) {
                $tipe = strtoupper($row->tipe_karyawan ?? 'PEGAWAI');
                if ($tipe === 'DOSEN') {
                    $badgeTipe = '<span class="badge ml-1 text-nowrap" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; white-space: nowrap;">DOSEN</span>';
                } else {
                    $badgeTipe = '<span class="badge ml-1 text-nowrap" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; white-space: nowrap;">' . e($tipe) . '</span>';
                }
                return '<div><strong class="text-dark" style="font-size: 0.9rem;">' . htmlspecialchars($row->nama) . '</strong> ' . $badgeTipe . '</div>' .
                       '<small class="text-muted">NIK: ' . ($row->nik ?? '-') . ' | ' . htmlspecialchars($row->unit->nama_unit ?? 'Unit Umum') . '</small>';
            })
            ->addColumn('tgl_bergabung_fmt', function ($row) {
                return $row->tgl_bergabung ? '<span class="font-weight-bold text-dark text-nowrap" style="font-size: 0.85rem; white-space: nowrap;">' . \Carbon\Carbon::parse($row->tgl_bergabung)->translatedFormat('d F Y') . '</span>' : '<span class="text-muted font-italic">-</span>';
            })
            ->addColumn('progress', function ($row) use ($onb) {
                $isDosen = strtolower($row->tipe_karyawan ?? 'tendik') === 'dosen';
                $masterIds = $isDosen ? $onb['dosenIds'] : $onb['tendikIds'];
                $total = count($masterIds);

                if ($total == 0) return '<span class="text-muted small font-italic">Belum ada tugas</span>';

                $completed = KaryawanOnboardingOffboarding::where('data_dosen_tendik_id', $row->id)
                    ->whereIn('master_onboarding_offboarding_id', $masterIds)
                    ->where('is_completed', true)
                    ->count();

                $percent = round(($completed / $total) * 100);
                $barGradient = $percent >= 50 ? 'linear-gradient(90deg, #094b54 0%, #0c6170 100%)' : 'linear-gradient(90deg, #d97706 0%, #f59e0b 100%)';
                $textColor = $percent >= 50 ? '#094b54' : '#b45309';

                return '
                <div style="min-width: 170px;">
                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.8rem;">
                        <span class="text-muted font-weight-bold">' . $completed . ' / ' . $total . ' Selesai</span>
                        <span class="font-weight-bold" style="color: ' . $textColor . ';">' . $percent . '%</span>
                    </div>
                    <div class="progress" style="height: 6px; border-radius: 10px; background-color: #f1f5f9; overflow: hidden;">
                        <div class="progress-bar" role="progressbar" style="width: ' . $percent . '%; background: ' . $barGradient . '; border-radius: 10px; transition: width 0.4s ease;"></div>
                    </div>
                </div>';
            })
            ->addColumn('status_badge', function () {
                return '<span class="badge badge-warning text-dark px-2 py-1 text-nowrap" style="white-space: nowrap; display: inline-flex; align-items: center;"><i class="fas fa-hourglass-half mr-1"></i> Sedang Proses</span>';
            })
            ->addColumn('aksi', function ($row) {
                return '<button type="button" class="btn btn-sm btn-modal-checklist" data-url="' . route('admin.pelaksanaan-onboarding-offboarding.detail', [$row->id, 'kategori' => 'onboarding']) . '" style="color: var(--tsu-primary, #094b54); background: #ffffff; border: 1.5px solid var(--tsu-primary-light, #cce6e9); border-radius: 8px; font-weight: 600; padding: 0.35rem 0.85rem; font-size: 0.82rem; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.05); white-space: nowrap;" onmouseover="this.style.background=\'var(--tsu-primary, #094b54)\';this.style.color=\'#fff\';" onmouseout="this.style.background=\'#fff\';this.style.color=\'var(--tsu-primary, #094b54)\';">
                    <i class="fas fa-tasks mr-1"></i> Buka Checklist
                </button>';
            })
            ->rawColumns(['identitas', 'tgl_bergabung_fmt', 'progress', 'status_badge', 'aksi'])
            ->make(true);
    }

    /**
     * DataTables Daftar Riwayat Onboarding (Sudah Lengkap 100%)
     */
    public function datatableRiwayatOnboarding(Request $request)
    {
        $onb = $this->getOnboardingTaskConfig();

        $query = DataDosenTendik::with(['unit'])
            ->where('is_active', 1)
            ->whereRaw("{$onb['completedSql']} >= {$onb['requiredSql']} AND {$onb['completedSql']} > 0");

        if ($request->filled('tipe_karyawan') && in_array(strtolower($request->tipe_karyawan), ['dosen', 'tendik'])) {
            $query->where('tipe_karyawan', $request->tipe_karyawan);
        }

        $query->orderBy('updated_at', 'desc')->orderBy('id', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('identitas', function ($row) {
                $tipe = strtoupper($row->tipe_karyawan ?? 'PEGAWAI');
                if ($tipe === 'DOSEN') {
                    $badgeTipe = '<span class="badge ml-1 text-nowrap" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; white-space: nowrap;">DOSEN</span>';
                } else {
                    $badgeTipe = '<span class="badge ml-1 text-nowrap" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; white-space: nowrap;">' . e($tipe) . '</span>';
                }
                return '<div><strong class="text-dark" style="font-size: 0.9rem;">' . htmlspecialchars($row->nama) . '</strong> ' . $badgeTipe . '</div>' .
                       '<small class="text-muted">NIK: ' . ($row->nik ?? '-') . ' | ' . htmlspecialchars($row->unit->nama_unit ?? 'Unit Umum') . '</small>';
            })
            ->addColumn('tgl_bergabung_fmt', function ($row) {
                return $row->tgl_bergabung ? '<span class="font-weight-bold text-dark text-nowrap" style="font-size: 0.85rem; white-space: nowrap;">' . \Carbon\Carbon::parse($row->tgl_bergabung)->translatedFormat('d F Y') . '</span>' : '<span class="text-muted font-italic">-</span>';
            })
            ->addColumn('status_badge', function () {
                return '<span class="badge badge-success px-2 py-1 text-nowrap" style="white-space: nowrap; display: inline-flex; align-items: center;"><i class="fas fa-check-circle mr-1"></i> Tuntas (100%)</span>';
            })
            ->addColumn('tgl_selesai_fmt', function ($row) {
                $lastCompleted = KaryawanOnboardingOffboarding::where('data_dosen_tendik_id', $row->id)
                    ->where('is_completed', 1)
                    ->orderBy('completed_at', 'desc')
                    ->value('completed_at');

                return $lastCompleted 
                    ? '<span class="text-muted text-nowrap" style="font-size: 0.82rem; white-space: nowrap;"><i class="far fa-calendar-check text-success mr-1"></i>' . \Carbon\Carbon::parse($lastCompleted)->translatedFormat('d M Y H:i') . '</span>'
                    : '<span class="text-muted font-italic text-nowrap" style="white-space: nowrap;">Selesai</span>';
            })
            ->addColumn('aksi', function ($row) {
                return '<button type="button" class="btn btn-sm btn-modal-checklist" data-url="' . route('admin.pelaksanaan-onboarding-offboarding.detail', [$row->id, 'kategori' => 'onboarding']) . '" style="color: #047857; background: #ffffff; border: 1.5px solid #a7f3d0; border-radius: 8px; font-weight: 600; padding: 0.35rem 0.85rem; font-size: 0.82rem; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.05); white-space: nowrap;" onmouseover="this.style.background=\'#047857\';this.style.color=\'#fff\';" onmouseout="this.style.background=\'#fff\';this.style.color=\'#047857\';">
                    <i class="fas fa-eye mr-1"></i> Lihat Checklist
                </button>';
            })
            ->rawColumns(['identitas', 'tgl_bergabung_fmt', 'status_badge', 'tgl_selesai_fmt', 'aksi'])
            ->make(true);
    }

    /**
     * DataTables Daftar Pegawai untuk Offboarding (Sedang Berjalan / Belum Lengkap)
     */
    public function datatableOffboarding(Request $request)
    {
        $off = $this->getOffboardingTaskConfig();

        $query = DataDosenTendik::with(['unit'])
            ->where('is_active', 0)
            ->whereRaw("{$off['completedSql']} < {$off['requiredSql']}");

        if ($request->filled('tipe_karyawan') && in_array(strtolower($request->tipe_karyawan), ['dosen', 'tendik'])) {
            $query->where('tipe_karyawan', $request->tipe_karyawan);
        }

        $query->orderBy('updated_at', 'desc')->orderBy('id', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('identitas', function ($row) {
                $tipe = strtoupper($row->tipe_karyawan ?? 'PEGAWAI');
                $badgeTipe = '<span class="badge ml-1 text-nowrap" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; white-space: nowrap;">' . e($tipe) . ' &bull; Nonaktif</span>';
                return '<div><strong class="text-dark" style="font-size: 0.9rem;">' . htmlspecialchars($row->nama) . '</strong> ' . $badgeTipe . '</div>' .
                       '<small class="text-muted">NIK: ' . ($row->nik ?? '-') . ' | ' . htmlspecialchars($row->unit->nama_unit ?? 'Unit Umum') . '</small>';
            })
            ->addColumn('progress', function ($row) use ($off) {
                $total = count($off['offboardIds']);
                if ($total == 0) return '<span class="text-muted small font-italic">Belum ada tugas</span>';

                $completed = KaryawanOnboardingOffboarding::where('data_dosen_tendik_id', $row->id)
                    ->whereIn('master_onboarding_offboarding_id', $off['offboardIds'])
                    ->where('is_completed', true)
                    ->count();

                $percent = round(($completed / $total) * 100);
                $barGradient = 'linear-gradient(90deg, #d97706 0%, #f59e0b 100%)';
                $textColor = '#b45309';

                return '
                <div style="min-width: 170px;">
                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.8rem;">
                        <span class="text-muted font-weight-bold">' . $completed . ' / ' . $total . ' Selesai</span>
                        <span class="font-weight-bold" style="color: ' . $textColor . ';">' . $percent . '%</span>
                    </div>
                    <div class="progress" style="height: 6px; border-radius: 10px; background-color: #f1f5f9; overflow: hidden;">
                        <div class="progress-bar" role="progressbar" style="width: ' . $percent . '%; background: ' . $barGradient . '; border-radius: 10px; transition: width 0.4s ease;"></div>
                    </div>
                </div>';
            })
            ->addColumn('status_badge', function () {
                return '<span class="badge badge-warning text-dark px-2 py-1 text-nowrap" style="white-space: nowrap; display: inline-flex; align-items: center;"><i class="fas fa-handshake mr-1"></i> Proses Serah Terima</span>';
            })
            ->addColumn('aksi', function ($row) {
                return '<button type="button" class="btn btn-sm btn-modal-checklist" data-url="' . route('admin.pelaksanaan-onboarding-offboarding.detail', [$row->id, 'kategori' => 'offboarding']) . '" style="color: #b45309; background: #ffffff; border: 1.5px solid #fde68a; border-radius: 8px; font-weight: 600; padding: 0.35rem 0.85rem; font-size: 0.82rem; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.05); white-space: nowrap;" onmouseover="this.style.background=\'#b45309\';this.style.color=\'#fff\';" onmouseout="this.style.background=\'#fff\';this.style.color=\'#b45309\';">
                    <i class="fas fa-clipboard-check mr-1"></i> Buka Checklist
                </button>';
            })
            ->rawColumns(['identitas', 'progress', 'status_badge', 'aksi'])
            ->make(true);
    }

    /**
     * DataTables Daftar Riwayat Offboarding (Serah Terima Sudah Lengkap 100%)
     */
    public function datatableRiwayatOffboarding(Request $request)
    {
        $off = $this->getOffboardingTaskConfig();

        $query = DataDosenTendik::with(['unit'])
            ->where('is_active', 0)
            ->whereRaw("{$off['completedSql']} >= {$off['requiredSql']} AND {$off['completedSql']} > 0");

        if ($request->filled('tipe_karyawan') && in_array(strtolower($request->tipe_karyawan), ['dosen', 'tendik'])) {
            $query->where('tipe_karyawan', $request->tipe_karyawan);
        }

        $query->orderBy('updated_at', 'desc')->orderBy('id', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('identitas', function ($row) {
                $tipe = strtoupper($row->tipe_karyawan ?? 'PEGAWAI');
                $badgeTipe = '<span class="badge ml-1 text-nowrap" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; white-space: nowrap;">' . e($tipe) . ' &bull; Nonaktif</span>';
                return '<div><strong class="text-dark" style="font-size: 0.9rem;">' . htmlspecialchars($row->nama) . '</strong> ' . $badgeTipe . '</div>' .
                       '<small class="text-muted">NIK: ' . ($row->nik ?? '-') . ' | ' . htmlspecialchars($row->unit->nama_unit ?? 'Unit Umum') . '</small>';
            })
            ->addColumn('status_badge', function () {
                return '<span class="badge badge-success px-2 py-1 text-nowrap" style="white-space: nowrap; display: inline-flex; align-items: center;"><i class="fas fa-check-double mr-1"></i> Serah Terima Tuntas</span>';
            })
            ->addColumn('tgl_selesai_fmt', function ($row) {
                $lastCompleted = KaryawanOnboardingOffboarding::where('data_dosen_tendik_id', $row->id)
                    ->where('is_completed', 1)
                    ->orderBy('completed_at', 'desc')
                    ->value('completed_at');

                return $lastCompleted 
                    ? '<span class="text-muted text-nowrap" style="font-size: 0.82rem; white-space: nowrap;"><i class="far fa-calendar-check text-success mr-1"></i>' . \Carbon\Carbon::parse($lastCompleted)->translatedFormat('d M Y H:i') . '</span>'
                    : '<span class="text-muted font-italic text-nowrap" style="white-space: nowrap;">Tuntas</span>';
            })
            ->addColumn('aksi', function ($row) {
                return '<button type="button" class="btn btn-sm btn-modal-checklist" data-url="' . route('admin.pelaksanaan-onboarding-offboarding.detail', [$row->id, 'kategori' => 'offboarding']) . '" style="color: #047857; background: #ffffff; border: 1.5px solid #a7f3d0; border-radius: 8px; font-weight: 600; padding: 0.35rem 0.85rem; font-size: 0.82rem; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.05); white-space: nowrap;" onmouseover="this.style.background=\'#047857\';this.style.color=\'#fff\';" onmouseout="this.style.background=\'#fff\';this.style.color=\'#047857\';">
                    <i class="fas fa-eye mr-1"></i> Lihat Checklist
                </button>';
            })
            ->rawColumns(['identitas', 'status_badge', 'tgl_selesai_fmt', 'aksi'])
            ->make(true);
    }

    /**
     * Modal Detail Checklist Pegawai
     */
    public function detail(Request $request, $karyawanId)
    {
        $kategori = $request->get('kategori', 'onboarding');
        $karyawan = DataDosenTendik::with('unit')->findOrFail($karyawanId);

        $tipe = strtolower($karyawan->tipe_karyawan ?? 'tendik');

        // Ambil daftar tugas relevan
        $tasks = MasterOnboardingOffboarding::active()
            ->where('kategori', $kategori)
            ->where(function ($q) use ($tipe) {
                $q->where('sasaran', 'semua')
                  ->orWhere('sasaran', $tipe);
            })
            ->orderBy('urutan', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Ambil riwayat checklist yang sudah dicentang
        $existingChecklists = KaryawanOnboardingOffboarding::with('verifikator')
            ->where('data_dosen_tendik_id', $karyawan->id)
            ->get()
            ->keyBy('master_onboarding_offboarding_id');

        return view('admin::onboarding-offboarding.detail_modal', compact('karyawan', 'kategori', 'tasks', 'existingChecklists'));
    }

    /**
     * AJAX Toggle Penyelesaian Checklist
     */
    public function toggleItem(Request $request)
    {
        $request->validate([
            'data_dosen_tendik_id'            => 'required|exists:data_dosen_tendiks,id',
            'master_onboarding_offboarding_id' => 'required|exists:master_onboarding_offboardings,id',
            'is_completed'                    => 'required|boolean',
            'catatan'                         => 'nullable|string',
        ]);

        $checklist = KaryawanOnboardingOffboarding::updateOrCreate(
            [
                'data_dosen_tendik_id'            => $request->data_dosen_tendik_id,
                'master_onboarding_offboarding_id' => $request->master_onboarding_offboarding_id,
            ],
            [
                'is_completed' => (bool) $request->is_completed,
                'completed_at' => $request->is_completed ? now() : null,
                'completed_by' => $request->is_completed ? Auth::id() : null,
                'catatan'      => $request->catatan,
            ]
        );

        $karyawan = DataDosenTendik::find($request->data_dosen_tendik_id);
        $master = MasterOnboardingOffboarding::find($request->master_onboarding_offboarding_id);
        $kategori = $master ? $master->kategori : 'onboarding';

        // Hitung apakah sekarang semua tugas telah lengkap (100%)
        $isAllCompleted = false;
        if ($kategori === 'onboarding') {
            $onb = $this->getOnboardingTaskConfig();
            $isDosen = strtolower($karyawan->tipe_karyawan ?? 'tendik') === 'dosen';
            $targetIds = $isDosen ? $onb['dosenIds'] : $onb['tendikIds'];
            $doneCount = KaryawanOnboardingOffboarding::where('data_dosen_tendik_id', $karyawan->id)
                ->whereIn('master_onboarding_offboarding_id', $targetIds)
                ->where('is_completed', 1)
                ->count();
            $isAllCompleted = (count($targetIds) > 0 && $doneCount >= count($targetIds));
        } else {
            $off = $this->getOffboardingTaskConfig();
            $targetIds = $off['offboardIds'];
            $doneCount = KaryawanOnboardingOffboarding::where('data_dosen_tendik_id', $karyawan->id)
                ->whereIn('master_onboarding_offboarding_id', $targetIds)
                ->where('is_completed', 1)
                ->count();
            $isAllCompleted = (count($targetIds) > 0 && $doneCount >= count($targetIds));
        }

        return response()->json([
            'success'          => true,
            'message'          => 'Status checklist berhasil diperbarui.',
            'kategori'         => $kategori,
            'is_all_completed' => $isAllCompleted,
            'stats'            => $this->calculateStats(),
            'data'             => $checklist,
        ]);
    }
}
