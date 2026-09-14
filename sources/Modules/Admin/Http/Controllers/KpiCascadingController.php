<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\KpiPeriode;
use App\Models\KpiMasterPerspektif;
use App\Models\KpiMasterIndikator;
use App\Models\KpiUnitIndikator;
use App\Models\MasterUnit;
use App\Traits\ApiResponseTrait;

class KpiCascadingController extends MiddlewareController
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

        // Calculate summary for selected unit & period
        $unitIndikators = KpiUnitIndikator::where('periode_id', $selectedPeriodeId)
            ->where('master_unit_id', $selectedUnitId)
            ->get();

        $totalBobot = $unitIndikators->sum('bobot');
        $totalIndikator = $unitIndikators->count();

        // Master Indikators for modal selection
        $masterIndikators = KpiMasterIndikator::with(['perspektif', 'parent'])
            ->where('is_active', 1)
            ->orderBy('kode_indikator', 'asc')
            ->get();

        return view('admin::kpi.cascading.index', [
            'title'             => 'Cascading KPI & Balanced Scorecard Unit Kerja',
            'menuIcon'          => 'fas fa-sitemap',
            'periodes'          => $periodes,
            'currentPeriode'    => $currentPeriode,
            'units'             => $units,
            'currentUnit'       => $currentUnit,
            'totalBobot'        => round($totalBobot, 2),
            'totalIndikator'    => $totalIndikator,
            'masterIndikators'  => $masterIndikators,
        ]);
    }

    public function dataTable(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $unitId = $request->input('master_unit_id');

        $query = KpiUnitIndikator::with([
            'masterIndikator.perspektif',
            'masterIndikator.parent',
            'parentUnitIndikator.unit',
            'parentUnitIndikator.masterIndikator',
            'childUnitIndikators.unit'
        ])
        ->where('periode_id', $periodeId)
        ->where('master_unit_id', $unitId)
        ->orderBy('urutan', 'asc')
        ->orderBy('id', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('perspektif_badge', function ($row) {
                if (!$row->masterIndikator || !$row->masterIndikator->perspektif) {
                    return '-';
                }
                $p = $row->masterIndikator->perspektif;
                $kode = strtoupper($p->kode ?? '');
                $style = match($kode) {
                    'FIN' => 'background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);',
                    'CUS' => 'background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25);',
                    'INT' => 'background: rgba(139, 92, 246, 0.1); color: #7c3aed; border: 1px solid rgba(139, 92, 246, 0.25);',
                    'LRN' => 'background: rgba(217, 119, 6, 0.1); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25);',
                    default => 'background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25);',
                };
                return '<span class="badge badge-pill font-weight-bold px-2 py-1" style="' . $style . ' font-size: 0.78rem;">' . htmlspecialchars($p->nama_perspektif) . '</span>';
            })
            ->addColumn('indikator_info', function ($row) {
                $mi = $row->masterIndikator;
                if (!$mi) return '-';

                $html = '<div class="font-weight-bold text-dark" style="font-size: 0.9rem;"><span style="color: #094b54; font-family: monospace; font-weight: 700;">[' . e($mi->kode_indikator) . ']</span> ' . e($mi->nama_indikator) . '</div>';
                if ($mi->level === 'sub') {
                    $html .= '<div class="mt-1"><span class="badge badge-pill px-2 py-0" style="background: rgba(2, 132, 199, 0.08); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.2); font-size: 0.72rem;"><i class="fas fa-level-down-alt mr-1"></i>Sub dari: ' . e(optional($mi->parent)->kode_indikator) . '</span></div>';
                }
                if ($row->keterkaitan_iku) {
                    $html .= '<div class="small text-muted mt-1"><i class="fas fa-bookmark mr-1" style="color: #094b54; font-size: 0.75rem;"></i>IKU: <strong>' . e($row->keterkaitan_iku) . '</strong></div>';
                }
                return $html;
            })
            ->addColumn('cascading_info', function ($row) {
                $jenis = $row->jenis_cascading ?? 'Direct';
                $badgeStyle = match($jenis) {
                    'Direct'       => 'background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25);',
                    'Contribution' => 'background: rgba(99, 102, 241, 0.1); color: #4f46e5; border: 1px solid rgba(99, 102, 241, 0.25);',
                    'Enabler'      => 'background: rgba(100, 116, 139, 0.1); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25);',
                    default        => 'background: #f1f5f9; color: #475569;',
                };
                $html = '<div><span class="badge badge-pill font-weight-bold px-2 py-1" style="' . $badgeStyle . ' font-size: 0.78rem;">' . htmlspecialchars($jenis) . '</span></div>';
                if ($row->parentUnitIndikator) {
                    $pUnit = optional($row->parentUnitIndikator->unit)->nama_unit ?? 'Pimpinan';
                    $pCode = optional($row->parentUnitIndikator->masterIndikator)->kode_indikator ?? '-';
                    $html .= '<div class="small text-muted mt-1" title="Diturunkan dari: '.$pUnit.'"><i class="fas fa-arrow-up text-secondary mr-1"></i>Dari: <strong>' . e($pUnit) . '</strong> (' . e($pCode) . ')</div>';
                }
                if ($row->childUnitIndikators && $row->childUnitIndikators->count() > 0) {
                    $html .= '<div class="small mt-1 font-weight-semibold" style="color: #059669;"><i class="fas fa-arrow-down mr-1"></i>Diturunkan ke ' . $row->childUnitIndikators->count() . ' unit</div>';
                }
                return $html;
            })
            ->addColumn('target_satuan', function ($row) {
                $targetVal = $row->target_angka !== null ? rtrim(rtrim(number_format($row->target_angka, 2, '.', ''), '0'), '.') : ($row->target_label ?? '-');
                $satuan = $row->satuan ?? optional($row->masterIndikator)->satuan ?? '';
                return '<strong class="text-dark" style="font-size: 0.95rem;">' . e($targetVal) . '</strong> <span class="text-muted small">' . e($satuan) . '</span>';
            })
            ->addColumn('roadmap_targets', function ($row) {
                if (!$row->target_2026 && !$row->target_2027 && !$row->target_2028 && !$row->target_2029) {
                    return '<span class="text-muted small font-italic">Tahunan</span>';
                }
                $html = '<div class="d-flex flex-wrap" style="gap: 4px; font-size: 0.75rem;">';
                if ($row->target_2026) $html .= '<span class="badge badge-light border text-dark">\'26: <b>'.e($row->target_2026).'</b></span>';
                if ($row->target_2027) $html .= '<span class="badge badge-light border text-dark">\'27: <b>'.e($row->target_2027).'</b></span>';
                if ($row->target_2028) $html .= '<span class="badge badge-light border text-dark">\'28: <b>'.e($row->target_2028).'</b></span>';
                if ($row->target_2029) $html .= '<span class="badge badge-light border text-dark">\'29: <b>'.e($row->target_2029).'</b></span>';
                $html .= '</div>';
                return $html;
            })
            ->addColumn('bobot_formatted', function ($row) {
                return '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); font-size: 0.85rem;">' . number_format($row->bobot, 1) . '%</span>';
            })
            ->addColumn('pic_info', function ($row) {
                $pic = $row->pic_data ?? '-';
                $unitTerkait = $row->unit_terkait ?? '';
                $html = '<div class="small"><strong>' . e($pic) . '</strong></div>';
                if ($unitTerkait) {
                    $html .= '<div class="small text-muted" style="font-size: 0.75rem;">' . e($unitTerkait) . '</div>';
                }
                return $html;
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="d-flex justify-content-center align-items-center" style="gap: 4px;">';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-info btn-cascade-down" data-id="'.$row->id.'" title="Turunkan ke Sub-Unit (Cascading)" style="border-radius: 6px; padding: 0.25rem 0.5rem;"><i class="fas fa-sitemap"></i></button>';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'" title="Edit Target & Bobot" style="border-radius: 6px; padding: 0.25rem 0.5rem;"><i class="fas fa-pencil-alt"></i></button>';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="'.$row->id.'" title="Hapus" style="border-radius: 6px; padding: 0.25rem 0.5rem;"><i class="fas fa-trash-alt"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['perspektif_badge', 'indikator_info', 'cascading_info', 'target_satuan', 'roadmap_targets', 'bobot_formatted', 'pic_info', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'periode_id'                => 'required|exists:kpi_periodes,id',
            'master_unit_id'            => 'required|exists:master_units,id',
            'master_indikator_id'       => 'required|exists:kpi_master_indikators,id',
            'parent_unit_indikator_id'  => 'nullable|exists:kpi_unit_indikators,id',
            'jenis_cascading'           => 'required|in:Direct,Contribution,Enabler',
            'target_angka'              => 'nullable|numeric',
            'target_label'              => 'nullable|string',
            'satuan'                    => 'nullable|string|max:50',
            'bobot'                     => 'required|numeric|min:0|max:100',
            'target_2026'               => 'nullable|string|max:50',
            'target_2027'               => 'nullable|string|max:50',
            'target_2028'               => 'nullable|string|max:50',
            'target_2029'               => 'nullable|string|max:50',
            'keterkaitan_iku'           => 'nullable|string|max:100',
            'sumber_data'               => 'nullable|string',
            'pic_data'                  => 'nullable|string|max:150',
            'unit_terkait'              => 'nullable|string',
            'keterangan'                => 'nullable|string',
        ]);

        $mIndikator = KpiMasterIndikator::find($validated['master_indikator_id']);
        $satuan = $validated['satuan'] ?: ($mIndikator ? $mIndikator->satuan : '');

        $unitIndikator = KpiUnitIndikator::create([
            'periode_id'                => $validated['periode_id'],
            'master_unit_id'            => $validated['master_unit_id'],
            'master_indikator_id'       => $validated['master_indikator_id'],
            'parent_unit_indikator_id'  => $validated['parent_unit_indikator_id'] ?? null,
            'jenis_cascading'           => $validated['jenis_cascading'],
            'target_angka'              => $validated['target_angka'] !== null ? $validated['target_angka'] : null,
            'target_label'              => $validated['target_label'] ?? null,
            'satuan'                    => $satuan,
            'bobot'                     => $validated['bobot'],
            'target_2026'               => $validated['target_2026'] ?? null,
            'target_2027'               => $validated['target_2027'] ?? null,
            'target_2028'               => $validated['target_2028'] ?? null,
            'target_2029'               => $validated['target_2029'] ?? null,
            'keterkaitan_iku'           => $validated['keterkaitan_iku'] ?? null,
            'sumber_data'               => $validated['sumber_data'] ?? null,
            'pic_data'                  => $validated['pic_data'] ?? null,
            'unit_terkait'              => $validated['unit_terkait'] ?? null,
            'status_monev'              => 'Belum Mengisi',
            'keterangan'                => $validated['keterangan'] ?? null,
        ]);

        return $this->successResponse($unitIndikator, 'Indikator KPI Unit berhasil ditambahkan ke Scorecard');
    }

    public function show($id)
    {
        $unitIndikator = KpiUnitIndikator::with([
            'masterIndikator.perspektif',
            'parentUnitIndikator.unit',
            'parentUnitIndikator.masterIndikator'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $unitIndikator,
        ]);
    }

    public function update(Request $request, $id)
    {
        $unitIndikator = KpiUnitIndikator::findOrFail($id);

        $validated = $request->validate([
            'master_indikator_id'       => 'required|exists:kpi_master_indikators,id',
            'parent_unit_indikator_id'  => 'nullable|exists:kpi_unit_indikators,id',
            'jenis_cascading'           => 'required|in:Direct,Contribution,Enabler',
            'target_angka'              => 'nullable|numeric',
            'target_label'              => 'nullable|string',
            'satuan'                    => 'nullable|string|max:50',
            'bobot'                     => 'required|numeric|min:0|max:100',
            'target_2026'               => 'nullable|string|max:50',
            'target_2027'               => 'nullable|string|max:50',
            'target_2028'               => 'nullable|string|max:50',
            'target_2029'               => 'nullable|string|max:50',
            'keterkaitan_iku'           => 'nullable|string|max:100',
            'sumber_data'               => 'nullable|string',
            'pic_data'                  => 'nullable|string|max:150',
            'unit_terkait'              => 'nullable|string',
            'keterangan'                => 'nullable|string',
        ]);

        $mIndikator = KpiMasterIndikator::find($validated['master_indikator_id']);
        $satuan = $validated['satuan'] ?: ($mIndikator ? $mIndikator->satuan : $unitIndikator->satuan);

        $unitIndikator->update([
            'master_indikator_id'       => $validated['master_indikator_id'],
            'parent_unit_indikator_id'  => $validated['parent_unit_indikator_id'] ?? null,
            'jenis_cascading'           => $validated['jenis_cascading'],
            'target_angka'              => $validated['target_angka'] !== null ? $validated['target_angka'] : null,
            'target_label'              => $validated['target_label'] ?? null,
            'satuan'                    => $satuan,
            'bobot'                     => $validated['bobot'],
            'target_2026'               => $validated['target_2026'] ?? null,
            'target_2027'               => $validated['target_2027'] ?? null,
            'target_2028'               => $validated['target_2028'] ?? null,
            'target_2029'               => $validated['target_2029'] ?? null,
            'keterkaitan_iku'           => $validated['keterkaitan_iku'] ?? null,
            'sumber_data'               => $validated['sumber_data'] ?? null,
            'pic_data'                  => $validated['pic_data'] ?? null,
            'unit_terkait'              => $validated['unit_terkait'] ?? null,
            'keterangan'                => $validated['keterangan'] ?? null,
        ]);

        // Recalculate if realisasi exists
        if ($unitIndikator->realisasi_angka !== null) {
            $unitIndikator->hitungCapaianDanSkor();
            $unitIndikator->save();
        }

        return $this->successResponse($unitIndikator, 'Indikator KPI Unit berhasil diperbarui');
    }

    public function destroy($id)
    {
        $unitIndikator = KpiUnitIndikator::findOrFail($id);

        if ($unitIndikator->childUnitIndikators()->count() > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Indikator ini sudah diturunkan (cascading) ke sub-unit lain. Hapus turunan di sub-unit terlebih dahulu!',
            ], 422);
        }

        $unitIndikator->delete();
        return $this->successResponse(null, 'Indikator KPI Unit berhasil dihapus');
    }

    /**
     * Action to cascade down to child unit
     */
    public function cascadeDown(Request $request)
    {
        $validated = $request->validate([
            'parent_unit_indikator_id' => 'required|exists:kpi_unit_indikators,id',
            'target_unit_id'           => 'required|exists:master_units,id',
            'jenis_cascading'          => 'required|in:Direct,Contribution,Enabler',
            'bobot'                    => 'required|numeric|min:0|max:100',
            'target_angka'             => 'nullable|numeric',
            'target_label'             => 'nullable|string',
            'satuan'                   => 'nullable|string',
            'pic_data'                 => 'nullable|string',
        ]);

        $parent = KpiUnitIndikator::with('masterIndikator')->findOrFail($validated['parent_unit_indikator_id']);

        $newChild = KpiUnitIndikator::create([
            'periode_id'                => $parent->periode_id,
            'master_unit_id'            => $validated['target_unit_id'],
            'master_indikator_id'       => $parent->master_indikator_id,
            'parent_unit_indikator_id'  => $parent->id,
            'jenis_cascading'           => $validated['jenis_cascading'],
            'target_angka'              => $validated['target_angka'] !== null ? $validated['target_angka'] : $parent->target_angka,
            'target_label'              => $validated['target_label'] ?? $parent->target_label,
            'satuan'                    => $validated['satuan'] ?: $parent->satuan,
            'bobot'                     => $validated['bobot'],
            'keterkaitan_iku'           => $parent->keterkaitan_iku,
            'sumber_data'               => $parent->sumber_data,
            'pic_data'                  => $validated['pic_data'] ?? null,
            'status_monev'              => 'Belum Mengisi',
        ]);

        return $this->successResponse($newChild, 'Indikator berhasil diturunkan (cascaded) ke unit tujuan');
    }

    /**
     * Options for parent indicators (from higher tier units)
     */
    public function getParentUnitIndikatorOptions(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $currentUnitId = $request->input('unit_id');

        // All indicators in the same period, excluding the current unit's own indicators
        $query = KpiUnitIndikator::with(['unit', 'masterIndikator'])
            ->where('periode_id', $periodeId);

        if ($currentUnitId) {
            $query->where('master_unit_id', '!=', $currentUnitId);
        }

        $items = $query->get()->map(function ($item) {
            return [
                'id'         => $item->id,
                'unit_name'  => optional($item->unit)->nama_unit ?? '-',
                'kode'       => optional($item->masterIndikator)->kode_indikator ?? '-',
                'nama'       => optional($item->masterIndikator)->nama_indikator ?? '-',
                'target'     => $item->target_angka !== null ? $item->target_angka . ' ' . $item->satuan : ($item->target_label ?? '-'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $items,
        ]);
    }
}
