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
        $selectedUnitId = $request->input('unit_id'); // null or 'all' or specific unit ID
        $currentUnit = ($selectedUnitId && $selectedUnitId !== 'all') ? MasterUnit::find($selectedUnitId) : null;

        $selectedCascading = $request->input('jenis_cascading');
        $search = $request->input('search');

        // Query data KPI
        $query = KpiUnitIndikator::with(['masterIndikator.perspektif', 'unit'])
            ->where('periode_id', $selectedPeriodeId);

        if ($selectedUnitId && $selectedUnitId !== 'all') {
            $query->where('master_unit_id', $selectedUnitId);
        }
        if ($selectedCascading && $selectedCascading !== 'all') {
            $query->where('jenis_cascading', $selectedCascading);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('masterIndikator', function($mq) use ($search) {
                    $mq->where('nama_indikator', 'like', "%{$search}%")
                       ->orWhere('kode_indikator', 'like', "%{$search}%");
                })->orWhere('unit_terkait', 'like', "%{$search}%")
                  ->orWhere('formula_pengukuran', 'like', "%{$search}%");
            });
        }

        $kpiItems = $query->orderBy('urutan', 'asc')->orderBy('id', 'asc')->get();

        // 4 Stat Cards
        $totalIndikator = $kpiItems->count();
        $avgCapaian = $totalIndikator > 0 ? round($kpiItems->whereNotNull('capaian_persen')->avg('capaian_persen') ?? 0, 1) : 0;
        $avgSkor = $totalIndikator > 0 ? round($kpiItems->whereNotNull('skor')->avg('skor') ?? 0, 1) : 0;
        $totalBobot = round($kpiItems->sum('bobot'), 1);

        // Master Indikators for modal selection
        $masterIndikators = KpiMasterIndikator::with(['perspektif', 'parent'])
            ->where('is_active', 1)
            ->orderBy('kode_indikator', 'asc')
            ->get();

        return view('admin::kpi.cascading.index', [
            'title'             => 'Data KPI Unit Kerja',
            'menuIcon'          => 'fas fa-chart-line',
            'periodes'          => $periodes,
            'currentPeriode'    => $currentPeriode,
            'units'             => $units,
            'currentUnit'       => $currentUnit,
            'selectedUnitId'    => $selectedUnitId,
            'selectedCascading' => $selectedCascading,
            'search'            => $search,
            'kpiItems'          => $kpiItems,
            'totalBobot'        => $totalBobot,
            'totalIndikator'    => $totalIndikator,
            'avgCapaian'        => $avgCapaian,
            'avgSkor'           => $avgSkor,
            'masterIndikators'  => $masterIndikators,
        ]);
    }

    public function dataTable(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $unitId = $request->input('master_unit_id');
        $cascading = $request->input('jenis_cascading');
        $search = $request->input('search');

        $query = KpiUnitIndikator::with([
            'masterIndikator.perspektif',
            'unit'
        ])
        ->where('periode_id', $periodeId);

        if ($unitId && $unitId !== 'all') {
            $query->where('master_unit_id', $unitId);
        }
        if ($cascading && $cascading !== 'all') {
            $query->where('jenis_cascading', $cascading);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('masterIndikator', function($mq) use ($search) {
                    $mq->where('nama_indikator', 'like', "%{$search}%");
                })->orWhere('unit_terkait', 'like', "%{$search}%")
                  ->orWhere('formula_pengukuran', 'like', "%{$search}%");
            });
        }

        $query->orderBy('urutan', 'asc')->orderBy('id', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('jenis_cascading_badge', function ($row) {
                $jenis = $row->jenis_cascading ?? 'Unit';
                $badgeClass = ($jenis === 'Program') ? 'badge-success' : 'badge-primary';
                return '<span class="badge ' . $badgeClass . ' px-2 py-1 font-weight-normal">' . htmlspecialchars($jenis) . '</span>';
            })
            ->addColumn('unit_info', function ($row) {
                return '<span class="font-weight-bold text-dark">' . htmlspecialchars($row->unit_terkait ?: optional($row->unit)->nama_unit ?: 'BAUK') . '</span>';
            })
            ->addColumn('definisi_indikator', function ($row) {
                return '<div class="font-weight-bold text-dark" style="font-size: 0.9rem;">' . e(optional($row->masterIndikator)->nama_indikator ?? '-') . '</div>';
            })
            ->addColumn('formula_pengukuran_col', function ($row) {
                return '<small class="text-muted font-italic">' . e($row->formula_pengukuran ?: '-') . '</small>';
            })
            ->addColumn('bukti_col', function ($row) {
                $bukti = $row->file_bukti ?: 'Laporan / PDF';
                return '<span class="badge badge-light border text-muted px-2 py-1"><i class="fas fa-file-alt mr-1"></i>' . e($bukti) . '</span>';
            })
            ->addColumn('target_col', function ($row) {
                $val = $row->target_angka !== null ? rtrim(rtrim(number_format($row->target_angka, 2, '.', ''), '0'), '.') : ($row->target_label ?? '-');
                $satuan = $row->satuan ?? '%';
                return '<strong class="text-dark">' . e($val) . '</strong> <small class="text-muted">' . e($satuan) . '</small>';
            })
            ->addColumn('bobot_col', function ($row) {
                return '<span class="badge badge-light border font-weight-bold text-dark px-2 py-1">' . number_format($row->bobot, 0) . '%</span>';
            })
            ->addColumn('realisasi_col', function ($row) {
                if ($row->realisasi_angka === null) return '<span class="text-muted small">-</span>';
                $val = rtrim(rtrim(number_format($row->realisasi_angka, 2, '.', ''), '0'), '.');
                $satuan = $row->satuan ?? '%';
                return '<strong class="text-primary">' . e($val) . '</strong> <small class="text-muted">' . e($satuan) . '</small>';
            })
            ->addColumn('capaian_col', function ($row) {
                if ($row->capaian_persen === null) return '<span class="text-muted small">-</span>';
                $val = number_format($row->capaian_persen, 1);
                $color = $row->capaian_persen >= 100 ? '#059669' : ($row->capaian_persen >= 80 ? '#0284c7' : '#d97706');
                return '<strong style="color: '.$color.';">' . $val . '%</strong>';
            })
            ->addColumn('skor_col', function ($row) {
                if ($row->skor === null) return '<span class="text-muted small">-</span>';
                return '<span class="badge badge-pill font-weight-bold px-2 py-1" style="background: rgba(9, 75, 84, 0.1); color: #094b54; font-size: 0.85rem;">' . number_format($row->skor, 1) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="d-flex justify-content-center align-items-center" style="gap: 5px;">';
                $btn .= '<button type="button" class="btn btn-xs btn-outline-success btn-realisasi" data-id="'.$row->id.'" data-target="'.$row->target_angka.'" data-bobot="'.$row->bobot.'" data-satuan="'.$row->satuan.'" data-nama="'.htmlspecialchars(optional($row->masterIndikator)->nama_indikator).'" title="Input Realisasi" style="border-radius: 6px; padding: 0.2rem 0.5rem;"><i class="fas fa-check-double"></i></button>';
                $btn .= '<button type="button" class="btn btn-xs btn-outline-primary btn-edit" data-id="'.$row->id.'" title="Edit KPI" style="border-radius: 6px; padding: 0.2rem 0.5rem;"><i class="fas fa-pencil-alt"></i></button>';
                $btn .= '<button type="button" class="btn btn-xs btn-outline-danger btn-delete" data-id="'.$row->id.'" title="Hapus" style="border-radius: 6px; padding: 0.2rem 0.5rem;"><i class="fas fa-trash-alt"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['jenis_cascading_badge', 'unit_info', 'definisi_indikator', 'formula_pengukuran_col', 'bukti_col', 'target_col', 'bobot_col', 'realisasi_col', 'capaian_col', 'skor_col', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'periode_id'         => 'required|exists:kpi_periodes,id',
            'master_unit_id'     => 'nullable|exists:master_units,id',
            'unit_terkait'       => 'nullable|string|max:100',
            'master_indikator_id'=> 'nullable|exists:kpi_master_indikators,id',
            'definisi_indikator' => 'nullable|string|max:255',
            'formula_pengukuran' => 'nullable|string|max:255',
            'file_bukti'         => 'nullable|string|max:255',
            'jenis_cascading'    => 'required|string|max:50',
            'target_angka'       => 'required|numeric',
            'bobot'              => 'required|numeric|min:0|max:100',
            'satuan'             => 'nullable|string|max:50',
        ]);

        // Cari atau buat master indikator jika diinput manual
        $masterIndikatorId = $validated['master_indikator_id'] ?? null;
        if (!$masterIndikatorId && !empty($validated['definisi_indikator'])) {
            $perspektif = KpiMasterPerspektif::first();
            $mIndikator = KpiMasterIndikator::create([
                'nama_indikator' => $validated['definisi_indikator'],
                'kode_indikator' => 'KPI-' . strtoupper(Str::random(6)),
                'perspektif_id'  => $perspektif ? $perspektif->id : 1,
                'satuan'         => $validated['satuan'] ?: '%',
                'level'          => 'induk',
                'is_active'      => true,
            ]);
            $masterIndikatorId = $mIndikator->id;
        }

        $unitIndikator = KpiUnitIndikator::create([
            'periode_id'         => $validated['periode_id'],
            'master_unit_id'     => $validated['master_unit_id'] ?? optional(MasterUnit::first())->id,
            'unit_terkait'       => $validated['unit_terkait'] ?: ($validated['master_unit_id'] ? optional(MasterUnit::find($validated['master_unit_id']))->nama_unit : 'BAUK'),
            'master_indikator_id'=> $masterIndikatorId,
            'jenis_cascading'    => $validated['jenis_cascading'],
            'formula_pengukuran' => $validated['formula_pengukuran'] ?? null,
            'file_bukti'         => $validated['file_bukti'] ?? 'Laporan / PDF',
            'target_angka'       => $validated['target_angka'],
            'target_label'       => (string)$validated['target_angka'],
            'satuan'             => $validated['satuan'] ?: '%',
            'bobot'              => $validated['bobot'],
            'status_monev'       => 'Belum Mengisi',
        ]);

        return $this->successResponse($unitIndikator, 'Indikator KPI Unit berhasil ditambahkan');
    }

    public function show($id)
    {
        $unitIndikator = KpiUnitIndikator::with([
            'masterIndikator.perspektif',
            'unit'
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
            'jenis_cascading'    => 'required|string|max:50',
            'unit_terkait'       => 'nullable|string|max:100',
            'formula_pengukuran' => 'nullable|string|max:255',
            'file_bukti'         => 'nullable|string|max:255',
            'target_angka'       => 'required|numeric',
            'bobot'              => 'required|numeric|min:0|max:100',
            'satuan'             => 'nullable|string|max:50',
        ]);

        $unitIndikator->update([
            'jenis_cascading'    => $validated['jenis_cascading'],
            'unit_terkait'       => $validated['unit_terkait'] ?? $unitIndikator->unit_terkait,
            'formula_pengukuran' => $validated['formula_pengukuran'] ?? $unitIndikator->formula_pengukuran,
            'file_bukti'         => $validated['file_bukti'] ?? $unitIndikator->file_bukti,
            'target_angka'       => $validated['target_angka'],
            'target_label'       => (string)$validated['target_angka'],
            'satuan'             => $validated['satuan'] ?: $unitIndikator->satuan,
            'bobot'              => $validated['bobot'],
        ]);

        if ($unitIndikator->realisasi_angka !== null) {
            $unitIndikator->hitungCapaianDanSkor();
            $unitIndikator->save();
        }

        return $this->successResponse($unitIndikator, 'Indikator KPI Unit berhasil diperbarui');
    }

    /**
     * Input Realisasi & Perhitungan Otomatis:
     * Capaian (%) = (Realisasi / Target) * 100%
     * Skor = (Capaian / 100) * Bobot
     */
    public function updateRealisasi(Request $request, $id)
    {
        $unitIndikator = KpiUnitIndikator::findOrFail($id);

        $request->validate([
            'realisasi_angka' => 'required|numeric',
            'file_bukti'      => 'nullable|string',
            'analisis_capaian'=> 'nullable|string',
        ]);

        $unitIndikator->realisasi_angka = $request->realisasi_angka;
        $unitIndikator->realisasi_label = (string) $request->realisasi_angka;
        if ($request->filled('file_bukti')) {
            $unitIndikator->file_bukti = $request->file_bukti;
        }
        if ($request->filled('analisis_capaian')) {
            $unitIndikator->analisis_capaian = $request->analisis_capaian;
        }
        $unitIndikator->status_monev = 'Tercapai';

        $unitIndikator->hitungCapaianDanSkor();
        $unitIndikator->save();

        return $this->successResponse($unitIndikator, 'Realisasi KPI berhasil disimpan dan capaian dihitung otomatis');
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
