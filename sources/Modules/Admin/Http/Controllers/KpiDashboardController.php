<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use App\Models\KpiPeriode;
use App\Models\KpiMasterPerspektif;
use App\Models\KpiUnitIndikator;
use App\Models\MasterUnit;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;

class KpiDashboardController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        // Permission check
        $this->registerPermissions('admin:kpi');
    }

    /**
     * Dashboard Eksekutif KPI & Balanced Scorecard
     */
    public function index(Request $request)
    {
        $periodes = KpiPeriode::orderBy('tahun', 'desc')->get();
        $selectedPeriodeId = $request->input('periode_id', optional(KpiPeriode::where('is_active', 1)->first())->id ?? optional($periodes->first())->id);
        $currentPeriode = KpiPeriode::find($selectedPeriodeId);

        // 4 BSC Perspektif Cards
        $perspektifs = KpiMasterPerspektif::orderBy('urutan', 'asc')->get();
        $perspektifStats = [];

        foreach ($perspektifs as $persp) {
            $query = KpiUnitIndikator::where('periode_id', $selectedPeriodeId)
                ->whereHas('masterIndikator', function ($q) use ($persp) {
                    $q->where('perspektif_id', $persp->id);
                });

            $totalIndikator = (clone $query)->count();
            $avgCapaian = (clone $query)->whereNotNull('capaian_persen')->avg('capaian_persen') ?? 0;
            $avgSkor = (clone $query)->whereNotNull('skor')->avg('skor') ?? 0;
            $totalBobot = (clone $query)->sum('bobot') ?? 0;

            $perspektifStats[] = [
                'perspektif'     => $persp,
                'total_indikator'=> $totalIndikator,
                'avg_capaian'    => round($avgCapaian, 2),
                'avg_skor'       => round($avgSkor, 2),
                'total_bobot'    => round($totalBobot, 2),
            ];
        }

        // Overall Aggregate Metrics
        $allIndikators = KpiUnitIndikator::where('periode_id', $selectedPeriodeId);
        $totalAllIndikator = (clone $allIndikators)->count();
        $overallAvgCapaian = (clone $allIndikators)->whereNotNull('capaian_persen')->avg('capaian_persen') ?? 0;
        $totalSkorTercapai = (clone $allIndikators)->whereNotNull('skor')->sum('skor') ?? 0;

        // Breakdown Per Unit
        $unitKpiStats = [];
        $unitsWithKpi = MasterUnit::whereHas('kpiIndikators', function ($q) use ($selectedPeriodeId) {
            $q->where('periode_id', $selectedPeriodeId);
        })->orderBy('nama_unit', 'asc')->get();

        foreach ($unitsWithKpi as $unit) {
            $uQuery = KpiUnitIndikator::where('periode_id', $selectedPeriodeId)
                ->where('master_unit_id', $unit->id);
            
            $uCount = (clone $uQuery)->count();
            $uCapaian = (clone $uQuery)->whereNotNull('capaian_persen')->avg('capaian_persen') ?? 0;
            $uSkor = (clone $uQuery)->whereNotNull('skor')->sum('skor') ?? 0;
            $uBobot = (clone $uQuery)->sum('bobot') ?? 0;

            $unitKpiStats[] = [
                'unit'        => $unit,
                'total_kpi'   => $uCount,
                'avg_capaian' => round($uCapaian, 2),
                'total_skor'  => round($uSkor, 2),
                'total_bobot' => round($uBobot, 2),
            ];
        }

        // Indikator Butuh Perhatian (Capaian < 70% dan sudah ada realisasi)
        $needAttention = KpiUnitIndikator::with(['masterIndikator.perspektif', 'unit'])
            ->where('periode_id', $selectedPeriodeId)
            ->whereNotNull('capaian_persen')
            ->where('capaian_persen', '<', 70)
            ->orderBy('capaian_persen', 'asc')
            ->take(5)
            ->get();

        // Top Indikator (Capaian >= 100%)
        $topAchievers = KpiUnitIndikator::with(['masterIndikator.perspektif', 'unit'])
            ->where('periode_id', $selectedPeriodeId)
            ->whereNotNull('capaian_persen')
            ->where('capaian_persen', '>=', 100)
            ->orderBy('capaian_persen', 'desc')
            ->take(5)
            ->get();

        return view('admin::kpi.dashboard.index', [
            'title'             => 'Dashboard KPI & Balanced Scorecard',
            'menuIcon'          => 'fas fa-tachometer-alt',
            'periodes'          => $periodes,
            'currentPeriode'    => $currentPeriode,
            'perspektifStats'   => $perspektifStats,
            'totalAllIndikator' => $totalAllIndikator,
            'overallAvgCapaian' => round($overallAvgCapaian, 2),
            'totalSkorTercapai' => round($totalSkorTercapai, 2),
            'unitKpiStats'      => $unitKpiStats,
            'needAttention'     => $needAttention,
            'topAchievers'      => $topAchievers,
        ]);
    }
}
