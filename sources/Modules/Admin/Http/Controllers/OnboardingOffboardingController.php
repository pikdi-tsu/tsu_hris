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
     * Dashboard Pemantauan Onboarding & Offboarding Pegawai
     */
    public function index()
    {
        // Hitung statistik ringkas
        $totalAktif = DataDosenTendik::where('is_active', 1)->count();
        $totalResign = DataDosenTendik::where('is_active', 0)->count();

        // Pegawai baru bergabung dalam 1 tahun terakhir (kandidat onboarding)
        $onboardingCount = DataDosenTendik::where('is_active', 1)
            ->where(function($q) {
                $q->where('tgl_bergabung', '>=', now()->subYear())
                  ->orWhereNull('tgl_bergabung');
            })->count();

        return view('admin::onboarding-offboarding.index', [
            'title'           => 'Pelaksanaan Onboarding & Offboarding Pegawai',
            'menuIcon'        => 'fas fa-user-check',
            'totalAktif'      => $totalAktif,
            'totalResign'     => $totalResign,
            'onboardingCount' => $onboardingCount,
        ]);
    }

    /**
     * DataTables Daftar Pegawai untuk Onboarding
     */
    public function datatableOnboarding(Request $request)
    {
        $query = DataDosenTendik::with(['unit'])
            ->where('is_active', 1)
            ->orderBy('tgl_bergabung', 'desc')
            ->orderBy('id', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('identitas', function ($row) {
                $tipe = strtoupper($row->tipe_karyawan ?? 'PEGAWAI');
                $badgeTipe = $tipe === 'DOSEN' ? 'badge-primary' : 'badge-info';
                return '<div><strong>' . htmlspecialchars($row->nama) . '</strong> <span class="badge ' . $badgeTipe . ' ml-1">' . $tipe . '</span></div>' .
                       '<small class="text-muted">NIK: ' . ($row->nik ?? '-') . ' | ' . htmlspecialchars($row->unit->nama_unit ?? 'Unit Umum') . '</small>';
            })
            ->addColumn('tgl_bergabung_fmt', function ($row) {
                return $row->tgl_bergabung ? \Carbon\Carbon::parse($row->tgl_bergabung)->format('d M Y') : '-';
            })
            ->addColumn('progress', function ($row) {
                $tipe = strtolower($row->tipe_karyawan ?? 'tendik');
                $masterIds = MasterOnboardingOffboarding::active()
                    ->onboarding()
                    ->where(function ($q) use ($tipe) {
                        $q->where('sasaran', 'semua')
                          ->orWhere('sasaran', $tipe);
                    })->pluck('id');

                $total = $masterIds->count();
                if ($total == 0) return '<span class="text-muted small">Belum ada tugas</span>';

                $completed = KaryawanOnboardingOffboarding::where('data_dosen_tendik_id', $row->id)
                    ->whereIn('master_onboarding_offboarding_id', $masterIds)
                    ->where('is_completed', true)
                    ->count();

                $percent = round(($completed / $total) * 100);
                $color = $percent >= 100 ? 'bg-success' : ($percent > 50 ? 'bg-info' : 'bg-warning');

                return '
                <div>
                    <div class="d-flex justify-content-between small font-weight-bold mb-1">
                        <span>' . $completed . ' / ' . $total . ' Selesai</span>
                        <span>' . $percent . '%</span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar ' . $color . '" role="progressbar" style="width: ' . $percent . '%;"></div>
                    </div>
                </div>';
            })
            ->addColumn('aksi', function ($row) {
                return '<button type="button" class="btn btn-sm btn-outline-primary btn-modal-checklist" data-url="' . route('admin.pelaksanaan-onboarding-offboarding.detail', [$row->id, 'kategori' => 'onboarding']) . '">
                    <i class="fas fa-tasks mr-1"></i> Buka Checklist
                </button>';
            })
            ->rawColumns(['identitas', 'progress', 'aksi'])
            ->make(true);
    }

    /**
     * DataTables Daftar Pegawai untuk Offboarding (Resign)
     */
    public function datatableOffboarding(Request $request)
    {
        $query = DataDosenTendik::with(['unit'])
            ->where('is_active', 0)
            ->orderBy('updated_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('identitas', function ($row) {
                $tipe = strtoupper($row->tipe_karyawan ?? 'PEGAWAI');
                return '<div><strong>' . htmlspecialchars($row->nama) . '</strong> <span class="badge badge-secondary ml-1">' . $tipe . ' (Nonaktif)</span></div>' .
                       '<small class="text-muted">NIK: ' . ($row->nik ?? '-') . ' | ' . htmlspecialchars($row->unit->nama_unit ?? 'Unit Umum') . '</small>';
            })
            ->addColumn('progress', function ($row) {
                $masterIds = MasterOnboardingOffboarding::active()
                    ->offboarding()
                    ->pluck('id');

                $total = $masterIds->count();
                if ($total == 0) return '<span class="text-muted small">Belum ada tugas</span>';

                $completed = KaryawanOnboardingOffboarding::where('data_dosen_tendik_id', $row->id)
                    ->whereIn('master_onboarding_offboarding_id', $masterIds)
                    ->where('is_completed', true)
                    ->count();

                $percent = round(($completed / $total) * 100);
                $color = $percent >= 100 ? 'bg-success' : ($percent > 50 ? 'bg-info' : 'bg-warning');

                return '
                <div>
                    <div class="d-flex justify-content-between small font-weight-bold mb-1">
                        <span>' . $completed . ' / ' . $total . ' Selesai</span>
                        <span>' . $percent . '%</span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar ' . $color . '" role="progressbar" style="width: ' . $percent . '%;"></div>
                    </div>
                </div>';
            })
            ->addColumn('aksi', function ($row) {
                return '<button type="button" class="btn btn-sm btn-outline-warning btn-modal-checklist" data-url="' . route('admin.pelaksanaan-onboarding-offboarding.detail', [$row->id, 'kategori' => 'offboarding']) . '">
                    <i class="fas fa-clipboard-check mr-1"></i> Buka Checklist
                </button>';
            })
            ->rawColumns(['identitas', 'progress', 'aksi'])
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

        return response()->json([
            'success' => true,
            'message' => 'Status checklist berhasil diperbarui.',
            'data'    => $checklist,
        ]);
    }
}
