<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\KpiMasterIndikator;
use App\Models\KpiMasterPerspektif;
use App\Traits\ApiResponseTrait;

class KpiMasterIndikatorController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:kpi');
    }

    public function index(Request $request)
    {
        $perspektifs = KpiMasterPerspektif::orderBy('urutan', 'asc')->get();
        $indukIndikators = KpiMasterIndikator::where('level', 'induk')
            ->orderBy('kode_indikator', 'asc')
            ->get();

        return view('admin::kpi.master-indikator.index', [
            'title'           => 'Kamus Master Indikator KPI',
            'menuIcon'        => 'fas fa-book-reader',
            'perspektifs'     => $perspektifs,
            'indukIndikators' => $indukIndikators,
        ]);
    }

    public function dataTable(Request $request)
    {
        $query = KpiMasterIndikator::with(['perspektif', 'parent'])
            ->withCount('subIndikators');

        if ($request->filled('perspektif_id')) {
            $query->where('perspektif_id', $request->perspektif_id);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $query->orderBy('kode_indikator', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('perspektif_badge', function ($row) {
                return $row->perspektif ? $row->perspektif->badge_html : '-';
            })
            ->addColumn('level_badge', function ($row) {
                if ($row->level === 'sub') {
                    return '<span class="badge badge-info"><i class="fas fa-level-down-alt mr-1"></i>Sub-Indikator</span>';
                }
                return '<span class="badge badge-primary"><i class="fas fa-folder mr-1"></i>Induk (' . $row->sub_indikators_count . ' sub)</span>';
            })
            ->addColumn('parent_name', function ($row) {
                if ($row->parent) {
                    return '<small class="text-muted"><i class="fas fa-arrow-up mr-1"></i>' . e($row->parent->kode_indikator) . ' - ' . e($row->parent->nama_indikator) . '</small>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('polaritas_badge', function ($row) {
                return $row->polaritas_badge;
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group btn-group-sm" role="group">';
                $btn .= '<button type="button" class="btn btn-info btn-detail" data-id="'.$row->id.'" title="Detail"><i class="fas fa-eye"></i></button>';
                $btn .= '<button type="button" class="btn btn-primary btn-edit" data-id="'.$row->id.'" title="Edit"><i class="fas fa-edit"></i></button>';
                $btn .= '<button type="button" class="btn btn-danger btn-delete" data-id="'.$row->id.'" title="Hapus"><i class="fas fa-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['perspektif_badge', 'level_badge', 'parent_name', 'polaritas_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'perspektif_id'         => 'required|exists:kpi_master_perspektifs,id',
            'kode_indikator'        => 'required|string|max:30|unique:kpi_master_indikators,kode_indikator',
            'nama_indikator'        => 'required|string',
            'level'                 => 'required|in:induk,sub',
            'parent_id'             => 'nullable|required_if:level,sub|exists:kpi_master_indikators,id',
            'deskripsi'             => 'nullable|string',
            'satuan'                => 'required|string|max:50',
            'polaritas'             => 'required|in:Maximize,Minimize,Stabilize',
            'formula_penghitungan'  => 'nullable|string',
            'tipe_target'           => 'required|in:Angka,Persentase,Rupiah,Waktu,Skala',
            'keterangan'            => 'nullable|string',
        ]);

        $indikator = KpiMasterIndikator::create([
            'perspektif_id'         => $validated['perspektif_id'],
            'parent_id'             => $validated['level'] === 'sub' ? $validated['parent_id'] : null,
            'level'                 => $validated['level'],
            'kode_indikator'        => $validated['kode_indikator'],
            'nama_indikator'        => $validated['nama_indikator'],
            'deskripsi'             => $validated['deskripsi'] ?? null,
            'satuan'                => $validated['satuan'],
            'polaritas'             => $validated['polaritas'],
            'formula_penghitungan'  => $validated['formula_penghitungan'] ?? null,
            'tipe_target'           => $validated['tipe_target'],
            'is_active'             => 1,
            'keterangan'            => $validated['keterangan'] ?? null,
        ]);

        return $this->successResponse($indikator, 'Indikator KPI berhasil ditambahkan');
    }

    public function show($id)
    {
        $indikator = KpiMasterIndikator::with(['perspektif', 'parent', 'subIndikators'])->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data'   => $indikator,
        ]);
    }

    public function update(Request $request, $id)
    {
        $indikator = KpiMasterIndikator::findOrFail($id);

        $validated = $request->validate([
            'perspektif_id'         => 'required|exists:kpi_master_perspektifs,id',
            'kode_indikator'        => 'required|string|max:30|unique:kpi_master_indikators,kode_indikator,' . $id,
            'nama_indikator'        => 'required|string',
            'level'                 => 'required|in:induk,sub',
            'parent_id'             => 'nullable|required_if:level,sub|exists:kpi_master_indikators,id',
            'deskripsi'             => 'nullable|string',
            'satuan'                => 'required|string|max:50',
            'polaritas'             => 'required|in:Maximize,Minimize,Stabilize',
            'formula_penghitungan'  => 'nullable|string',
            'tipe_target'           => 'required|in:Angka,Persentase,Rupiah,Waktu,Skala',
            'keterangan'            => 'nullable|string',
        ]);

        $indikator->update([
            'perspektif_id'         => $validated['perspektif_id'],
            'parent_id'             => $validated['level'] === 'sub' ? $validated['parent_id'] : null,
            'level'                 => $validated['level'],
            'kode_indikator'        => $validated['kode_indikator'],
            'nama_indikator'        => $validated['nama_indikator'],
            'deskripsi'             => $validated['deskripsi'] ?? null,
            'satuan'                => $validated['satuan'],
            'polaritas'             => $validated['polaritas'],
            'formula_penghitungan'  => $validated['formula_penghitungan'] ?? null,
            'tipe_target'           => $validated['tipe_target'],
            'keterangan'            => $validated['keterangan'] ?? null,
        ]);

        return $this->successResponse($indikator, 'Indikator KPI berhasil diperbarui');
    }

    public function destroy($id)
    {
        $indikator = KpiMasterIndikator::findOrFail($id);

        if ($indikator->unitIndikators()->count() > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Indikator ini sudah dipakai dalam Cascading Unit Kerja dan tidak dapat dihapus!',
            ], 422);
        }

        if ($indikator->subIndikators()->count() > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Indikator ini memiliki sub-indikator di bawahnya. Hapus atau pindahkan sub-indikator terlebih dahulu!',
            ], 422);
        }

        $indikator->delete();
        return $this->successResponse(null, 'Indikator KPI berhasil dihapus');
    }

    public function getParentOptions(Request $request)
    {
        $query = KpiMasterIndikator::where('level', 'induk');

        if ($request->filled('perspektif_id')) {
            $query->where('perspektif_id', $request->perspektif_id);
        }

        if ($request->filled('exclude_id')) {
            $query->where('id', '!=', $request->exclude_id);
        }

        $options = $query->orderBy('kode_indikator', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $options,
        ]);
    }
}
