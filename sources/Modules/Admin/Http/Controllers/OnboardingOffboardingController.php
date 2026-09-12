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

        $totalTasks = MasterOnboardingOffboarding::where('is_active', 1)->count();

        return view('admin::onboarding-offboarding.index', [
            'title'           => 'Pelaksanaan Onboarding & Offboarding Pegawai',
            'menuIcon'        => 'fas fa-user-check',
            'totalAktif'      => $totalAktif,
            'totalResign'     => $totalResign,
            'onboardingCount' => $onboardingCount,
            'totalTasks'      => $totalTasks,
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
                if ($tipe === 'DOSEN') {
                    $badgeTipe = '<span class="badge ml-1" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">DOSEN</span>';
                } else {
                    $badgeTipe = '<span class="badge ml-1" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">' . e($tipe) . '</span>';
                }
                return '<div><strong class="text-dark" style="font-size: 0.9rem;">' . htmlspecialchars($row->nama) . '</strong> ' . $badgeTipe . '</div>' .
                       '<small class="text-muted">NIK: ' . ($row->nik ?? '-') . ' | ' . htmlspecialchars($row->unit->nama_unit ?? 'Unit Umum') . '</small>';
            })
            ->addColumn('tgl_bergabung_fmt', function ($row) {
                return $row->tgl_bergabung ? '<span class="font-weight-bold text-dark" style="font-size: 0.85rem;">' . \Carbon\Carbon::parse($row->tgl_bergabung)->translatedFormat('d F Y') . '</span>' : '<span class="text-muted font-italic">-</span>';
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
                if ($total == 0) return '<span class="text-muted small font-italic">Belum ada tugas</span>';

                $completed = KaryawanOnboardingOffboarding::where('data_dosen_tendik_id', $row->id)
                    ->whereIn('master_onboarding_offboarding_id', $masterIds)
                    ->where('is_completed', true)
                    ->count();

                $percent = round(($completed / $total) * 100);
                if ($percent >= 100) {
                    $barGradient = 'linear-gradient(90deg, #047857 0%, #10b981 100%)';
                    $textColor = '#047857';
                } elseif ($percent >= 50) {
                    $barGradient = 'linear-gradient(90deg, #094b54 0%, #0c6170 100%)';
                    $textColor = '#094b54';
                } else {
                    $barGradient = 'linear-gradient(90deg, #d97706 0%, #f59e0b 100%)';
                    $textColor = '#b45309';
                }

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
            ->addColumn('aksi', function ($row) {
                return '<button type="button" class="btn btn-sm btn-modal-checklist" data-url="' . route('admin.pelaksanaan-onboarding-offboarding.detail', [$row->id, 'kategori' => 'onboarding']) . '" style="color: var(--tsu-primary, #094b54); background: #ffffff; border: 1.5px solid var(--tsu-primary-light, #cce6e9); border-radius: 8px; font-weight: 600; padding: 0.35rem 0.85rem; font-size: 0.82rem; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.05);" onmouseover="this.style.background=\'var(--tsu-primary, #094b54)\';this.style.color=\'#fff\';" onmouseout="this.style.background=\'#fff\';this.style.color=\'var(--tsu-primary, #094b54)\';">
                    <i class="fas fa-tasks mr-1"></i> Buka Checklist
                </button>';
            })
            ->rawColumns(['identitas', 'tgl_bergabung_fmt', 'progress', 'aksi'])
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
                $badgeTipe = '<span class="badge ml-1" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">' . e($tipe) . ' &bull; Nonaktif</span>';
                return '<div><strong class="text-dark" style="font-size: 0.9rem;">' . htmlspecialchars($row->nama) . '</strong> ' . $badgeTipe . '</div>' .
                       '<small class="text-muted">NIK: ' . ($row->nik ?? '-') . ' | ' . htmlspecialchars($row->unit->nama_unit ?? 'Unit Umum') . '</small>';
            })
            ->addColumn('progress', function ($row) {
                $masterIds = MasterOnboardingOffboarding::active()
                    ->offboarding()
                    ->pluck('id');

                $total = $masterIds->count();
                if ($total == 0) return '<span class="text-muted small font-italic">Belum ada tugas</span>';

                $completed = KaryawanOnboardingOffboarding::where('data_dosen_tendik_id', $row->id)
                    ->whereIn('master_onboarding_offboarding_id', $masterIds)
                    ->where('is_completed', true)
                    ->count();

                $percent = round(($completed / $total) * 100);
                if ($percent >= 100) {
                    $barGradient = 'linear-gradient(90deg, #047857 0%, #10b981 100%)';
                    $textColor = '#047857';
                } elseif ($percent >= 50) {
                    $barGradient = 'linear-gradient(90deg, #094b54 0%, #0c6170 100%)';
                    $textColor = '#094b54';
                } else {
                    $barGradient = 'linear-gradient(90deg, #d97706 0%, #f59e0b 100%)';
                    $textColor = '#b45309';
                }

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
            ->addColumn('aksi', function ($row) {
                return '<button type="button" class="btn btn-sm btn-modal-checklist" data-url="' . route('admin.pelaksanaan-onboarding-offboarding.detail', [$row->id, 'kategori' => 'offboarding']) . '" style="color: #b45309; background: #ffffff; border: 1.5px solid #fde68a; border-radius: 8px; font-weight: 600; padding: 0.35rem 0.85rem; font-size: 0.82rem; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.05);" onmouseover="this.style.background=\'#b45309\';this.style.color=\'#fff\';" onmouseout="this.style.background=\'#fff\';this.style.color=\'#b45309\';">
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
