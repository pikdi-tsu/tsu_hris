<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\KpiPeriode;
use App\Models\KpiUnitIndikator;
use App\Models\MasterUnit;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Storage;

class KpiMonitoringController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:kpi');
    }

    public function index(Request $request)
    {
        $periodes = KpiPeriode::orderBy('tahun', 'desc')->get();
        $selectedPeriodeId = $request->input('periode_id', optional(KpiPeriode::where('is_active', 1)->first())->id ?? optional($periodes->first())->id);
        $currentPeriode = KpiPeriode::find($selectedPeriodeId);

        $units = MasterUnit::orderBy('nama_unit', 'asc')->get();
        $selectedUnitId = $request->input('unit_id', optional($units->first())->id);
        $currentUnit = MasterUnit::find($selectedUnitId);

        // Stats for this unit & period
        $unitIndikators = KpiUnitIndikator::where('periode_id', $selectedPeriodeId)
            ->where('master_unit_id', $selectedUnitId)
            ->get();

        $totalIndikator = $unitIndikators->count();
        $totalTerisi = $unitIndikators->whereNotNull('realisasi_angka')->count();
        $avgCapaian = $unitIndikators->whereNotNull('capaian_persen')->avg('capaian_persen') ?? 0;
        $totalSkor = $unitIndikators->whereNotNull('skor')->sum('skor') ?? 0;

        return view('admin::kpi.monitoring.index', [
            'title'          => 'Monitoring & Realisasi Kinerja KPI',
            'menuIcon'       => 'fas fa-chart-line',
            'periodes'       => $periodes,
            'currentPeriode' => $currentPeriode,
            'units'          => $units,
            'currentUnit'    => $currentUnit,
            'totalIndikator' => $totalIndikator,
            'totalTerisi'    => $totalTerisi,
            'avgCapaian'     => round($avgCapaian, 2),
            'totalSkor'      => round($totalSkor, 2),
        ]);
    }

    public function dataTable(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $unitId = $request->input('master_unit_id');

        $query = KpiUnitIndikator::with([
            'masterIndikator.perspektif',
            'masterIndikator.parent',
            'unit'
        ])
        ->where('periode_id', $periodeId)
        ->where('master_unit_id', $unitId)
        ->orderBy('urutan', 'asc')
        ->orderBy('id', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('perspektif_badge', function ($row) {
                return $row->masterIndikator && $row->masterIndikator->perspektif 
                    ? $row->masterIndikator->perspektif->badge_html 
                    : '-';
            })
            ->addColumn('indikator_info', function ($row) {
                $mi = $row->masterIndikator;
                if (!$mi) return '-';

                $html = '<div class="font-weight-bold text-dark">[' . e($mi->kode_indikator) . '] ' . e($mi->nama_indikator) . '</div>';
                if ($mi->level === 'sub') {
                    $html .= '<span class="badge badge-light-info text-info border border-info px-2 py-0" style="font-size: 11px;"><i class="fas fa-level-down-alt mr-1"></i>Sub-Indikator</span>';
                }
                $html .= ' ' . $mi->polaritas_badge;
                return $html;
            })
            ->addColumn('bobot_formatted', function ($row) {
                return '<span class="badge badge-secondary px-2 py-1">' . number_format($row->bobot, 1) . '%</span>';
            })
            ->addColumn('target_satuan', function ($row) {
                $targetVal = $row->target_angka !== null ? rtrim(rtrim(number_format($row->target_angka, 2, '.', ''), '0'), '.') : ($row->target_label ?? '-');
                $satuan = $row->satuan ?? optional($row->masterIndikator)->satuan ?? '';
                return '<strong>' . e($targetVal) . '</strong> <small class="text-muted">' . e($satuan) . '</small>';
            })
            ->addColumn('realisasi_satuan', function ($row) {
                if ($row->realisasi_angka === null && !$row->realisasi_label) {
                    return '<span class="badge badge-light text-muted font-italic">Belum Diisi</span>';
                }
                $realVal = $row->realisasi_angka !== null ? rtrim(rtrim(number_format($row->realisasi_angka, 2, '.', ''), '0'), '.') : $row->realisasi_label;
                $satuan = $row->satuan ?? optional($row->masterIndikator)->satuan ?? '';
                return '<strong class="text-primary">' . e($realVal) . '</strong> <small class="text-muted">' . e($satuan) . '</small>';
            })
            ->addColumn('capaian_badge', function ($row) {
                return $row->capaian_badge;
            })
            ->addColumn('skor_formatted', function ($row) {
                if ($row->skor === null) {
                    return '<span class="text-muted">-</span>';
                }
                return '<strong class="text-success" style="font-size: 14px;">' . number_format($row->skor, 2) . '</strong>';
            })
            ->addColumn('bukti_dukung', function ($row) {
                if ($row->file_bukti) {
                    $url = asset('storage/' . $row->file_bukti);
                    return '<a href="'.$url.'" target="_blank" class="btn btn-xs btn-outline-info" title="Lihat Bukti Dukung"><i class="fas fa-file-alt mr-1"></i>Bukti</a>';
                }
                return '<span class="text-muted small font-italic">Tidak Ada</span>';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status_monev_badge;
            })
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-success btn-evaluasi" data-id="'.$row->id.'" title="Input / Update Realisasi Kinerja"><i class="fas fa-clipboard-check mr-1"></i>Evaluasi</button>';
                return $btn;
            })
            ->rawColumns(['perspektif_badge', 'indikator_info', 'bobot_formatted', 'target_satuan', 'realisasi_satuan', 'capaian_badge', 'skor_formatted', 'bukti_dukung', 'status_badge', 'action'])
            ->make(true);
    }

    public function updateRealisasi(Request $request, $id)
    {
        $unitIndikator = KpiUnitIndikator::with('masterIndikator')->findOrFail($id);

        $validated = $request->validate([
            'realisasi_angka'        => 'nullable|numeric',
            'realisasi_label'        => 'nullable|string',
            'analisis_capaian'       => 'nullable|string',
            'kendala'                => 'nullable|string',
            'rencana_tindak_lanjut'  => 'nullable|string',
            'status_monev'           => 'required|in:Belum Mengisi,Draft,Terevaluasi,Tercapai,Tidak Tercapai',
            'file_bukti'             => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,xlsx,zip|max:10240',
        ]);

        if ($request->hasFile('file_bukti')) {
            // Delete old file if exists
            if ($unitIndikator->file_bukti && Storage::disk('public')->exists($unitIndikator->file_bukti)) {
                Storage::disk('public')->delete($unitIndikator->file_bukti);
            }
            $filePath = $request->file('file_bukti')->store('kpi_evidence', 'public');
            $unitIndikator->file_bukti = $filePath;
        }

        $unitIndikator->realisasi_angka = $validated['realisasi_angka'] !== null ? $validated['realisasi_angka'] : null;
        $unitIndikator->realisasi_label = $validated['realisasi_label'] ?? null;
        $unitIndikator->analisis_capaian = $validated['analisis_capaian'] ?? null;
        $unitIndikator->kendala = $validated['kendala'] ?? null;
        $unitIndikator->rencana_tindak_lanjut = $validated['rencana_tindak_lanjut'] ?? null;
        $unitIndikator->status_monev = $validated['status_monev'];

        // Compute Capaian % and Skor automatically
        $unitIndikator->hitungCapaianDanSkor();
        $unitIndikator->save();

        return $this->successResponse([
            'unit_indikator' => $unitIndikator,
            'capaian_persen' => $unitIndikator->capaian_persen,
            'skor'           => $unitIndikator->skor,
        ], 'Realisasi dan evaluasi capaian KPI berhasil disimpan');
    }
}
