<?php

namespace Modules\Admin\Http\Controllers\Rkat;

use App\Http\Controllers\MiddlewareController;
use App\Models\MasterUnit;
use App\Models\RkatApprovalLog;
use App\Models\RkatPengajuan;
use App\Models\RkatPeriode;
use App\Models\RkatProgram;
use Illuminate\Http\Request;

class RkatDashboardController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:rkat');
    }

    /**
     * Dashboard Utama RKAT & Anggaran
     */
    public function index(Request $request)
    {
        $this->guard('view', 'admin:rkat');

        $periodes = RkatPeriode::orderBy('tahun_anggaran', 'desc')->get();
        $selectedPeriodeId = $request->input('periode_id');
        if (!$selectedPeriodeId) {
            $activePeriode = RkatPeriode::active()->first();
            if ($activePeriode) {
                $selectedPeriodeId = $activePeriode->id;
            } else {
                $currentYearPeriode = $periodes->firstWhere('tahun_anggaran', date('Y'));
                if ($currentYearPeriode) {
                    $selectedPeriodeId = $currentYearPeriode->id;
                } else {
                    $withData = RkatPeriode::has('pengajuans')->first();
                    $selectedPeriodeId = $withData ? $withData->id : optional($periodes->first())->id;
                }
            }
        }
        $selectedPeriode = RkatPeriode::find($selectedPeriodeId);

        $selectedUnitId = $request->input('unit_id');
        $units = MasterUnit::orderBy('nama_unit')->get();

        // Base query pengajuan
        $query = RkatPengajuan::with(['program', 'unit'])
            ->when($selectedPeriodeId, function ($q) use ($selectedPeriodeId) {
                $q->where('periode_id', $selectedPeriodeId);
            })
            ->when($selectedUnitId, function ($q) use ($selectedUnitId) {
                $q->where('unit_id', $selectedUnitId);
            });

        $pengajuans = $query->get();

        // Indikator Utama
        $totalPengajuan = $pengajuans->sum('total_anggaran_diajukan');
        $totalDisetujui = $pengajuans->where('status', 'Disetujui')->sum('total_anggaran_disetujui');
        $totalRealisasi = $pengajuans->sum('total_realisasi');
        $sisaAnggaran = max(0, $totalDisetujui - $totalRealisasi);
        $persenSerapan = $totalDisetujui > 0 ? round(($totalRealisasi / $totalDisetujui) * 100, 1) : 0;

        // Status RKAT
        $statusCounts = [
            'Draft' => $pengajuans->where('status', 'Draft')->count(),
            'Diajukan' => $pengajuans->where('status', 'Diajukan')->count(),
            'Review' => $pengajuans->where('status', 'Review')->count(),
            'Disetujui' => $pengajuans->where('status', 'Disetujui')->count(),
            'Revisi' => $pengajuans->where('status', 'Revisi')->count(),
            'Ditolak' => $pengajuans->where('status', 'Ditolak')->count(),
        ];

        // Komposisi Anggaran per Program
        $programs = RkatProgram::where('is_active', 1)->get();
        $programChart = [];
        foreach ($programs as $prog) {
            $nominal = $pengajuans->where('program_id', $prog->id)->sum('total_anggaran_disetujui');
            if ($nominal == 0) {
                $nominal = $pengajuans->where('program_id', $prog->id)->sum('total_anggaran_diajukan');
            }
            if ($nominal > 0) {
                $programChart[] = [
                    'name' => $prog->nama_program,
                    'total' => $nominal,
                ];
            }
        }

        // Realisasi Anggaran per Unit Kerja (Top 6 Units)
        $unitChart = [];
        foreach ($units as $u) {
            $uPengajuans = $pengajuans->where('unit_id', $u->id);
            $anggaran = (float) $uPengajuans->where('status', 'Disetujui')->sum('total_anggaran_disetujui');
            if ($anggaran == 0) {
                $anggaran = (float) $uPengajuans->sum('total_anggaran_diajukan');
            }
            $realisasi = (float) $uPengajuans->sum('total_realisasi');
            if ($anggaran > 0 || $realisasi > 0) {
                $unitChart[] = [
                    'name' => $u->nama_unit,
                    'anggaran' => $anggaran,
                    'realisasi' => $realisasi,
                ];
            }
        }

        // Aktivitas Terbaru (Audit Trail)
        $recentActivities = RkatApprovalLog::with(['pengajuan.unit', 'user'])
            ->when($selectedPeriodeId, function ($q) use ($selectedPeriodeId) {
                $q->whereHas('pengajuan', function ($sq) use ($selectedPeriodeId) {
                    $sq->where('periode_id', $selectedPeriodeId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('admin::rkat.dashboard', [
            'title' => 'Dashboard RKAT & Anggaran',
            'periodes' => $periodes,
            'selectedPeriode' => $selectedPeriode,
            'selectedPeriodeId' => $selectedPeriodeId,
            'units' => $units,
            'selectedUnitId' => $selectedUnitId,
            'totalPengajuan' => $totalPengajuan,
            'totalDisetujui' => $totalDisetujui,
            'totalRealisasi' => $totalRealisasi,
            'sisaAnggaran' => $sisaAnggaran,
            'persenSerapan' => $persenSerapan,
            'statusCounts' => $statusCounts,
            'programChart' => $programChart,
            'unitChart' => $unitChart,
            'recentActivities' => $recentActivities,
            'totalKegiatan' => $pengajuans->count(),
        ]);
    }
}
