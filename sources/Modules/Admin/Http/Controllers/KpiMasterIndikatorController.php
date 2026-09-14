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

        $counts = [
            'total'      => KpiMasterIndikator::count(),
            'induk'      => KpiMasterIndikator::where('level', 'induk')->count(),
            'sub'        => KpiMasterIndikator::where('level', 'sub')->count(),
            'perspektif' => KpiMasterPerspektif::count(),
        ];

        return view('admin::kpi.master-indikator.index', [
            'title'           => 'Kamus Master Indikator KPI',
            'menuIcon'        => 'fas fa-book-reader',
            'perspektifs'     => $perspektifs,
            'indukIndikators' => $indukIndikators,
            'counts'          => $counts,
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
                if (!$row->perspektif) return '-';
                $kode = strtoupper($row->perspektif->kode ?? '');
                $style = match($kode) {
                    'FIN' => 'background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);',
                    'CUS' => 'background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25);',
                    'INT' => 'background: rgba(139, 92, 246, 0.1); color: #7c3aed; border: 1px solid rgba(139, 92, 246, 0.25);',
                    'LRN' => 'background: rgba(217, 119, 6, 0.1); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25);',
                    default => 'background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25);',
                };
                return '<span class="badge" style="' . $style . ' border-radius: 9999px; font-weight: 600; padding: 4px 10px;">' . htmlspecialchars($row->perspektif->nama_perspektif) . '</span>';
            })
            ->addColumn('level_badge', function ($row) {
                if ($row->level === 'sub') {
                    return '<span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Sub-Indikator</span>';
                }
                return '<span class="badge" style="background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Induk (' . $row->sub_indikators_count . ' sub)</span>';
            })
            ->addColumn('parent_name', function ($row) {
                if ($row->parent) {
                    return '<div class="small font-weight-bold text-dark">[' . e($row->parent->kode_indikator) . ']</div><div class="small text-muted">' . e($row->parent->nama_indikator) . '</div>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('polaritas_badge', function ($row) {
                return match(strtolower($row->polaritas ?? 'maximize')) {
                    'minimize' => '<span class="badge" style="background: rgba(217, 119, 6, 0.1); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Minimize</span>',
                    'stabilize'=> '<span class="badge" style="background: rgba(100, 116, 139, 0.1); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Stabilize</span>',
                    default    => '<span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Maximize</span>',
                };
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="d-flex justify-content-center align-items-center" style="gap: 5px;">';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-info btn-detail" data-id="'.$row->id.'" title="Detail" style="border-radius: 6px; padding: 3px 8px; font-size: 0.8rem;"><i class="fas fa-eye"></i></button>';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'" title="Edit" style="border-radius: 6px; padding: 3px 8px; font-size: 0.8rem;"><i class="fas fa-pencil-alt"></i></button>';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="'.$row->id.'" title="Hapus" style="border-radius: 6px; padding: 3px 8px; font-size: 0.8rem;"><i class="fas fa-trash"></i></button>';
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
