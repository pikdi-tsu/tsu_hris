<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\KpiMasterPerspektif;
use App\Traits\ApiResponseTrait;

class KpiMasterPerspektifController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:kpi');
    }

    public function index()
    {
        $counts = [
            'total'      => KpiMasterPerspektif::count(),
            'aktif'      => KpiMasterPerspektif::where('is_active', 1)->count(),
            'indikator'  => \App\Models\KpiMasterIndikator::count(),
            'induk'      => \App\Models\KpiMasterIndikator::where('level', 'induk')->count(),
        ];

        return view('admin::kpi.perspektif.index', [
            'title'    => 'Master Perspektif Balanced Scorecard (BSC)',
            'menuIcon' => 'fas fa-layer-group',
            'counts'   => $counts,
        ]);
    }

    public function dataTable()
    {
        $query = KpiMasterPerspektif::withCount('indikators')->orderBy('urutan', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nama_perspektif_fmt', function ($row) {
                $countText = $row->indikators_count . ' Indikator KPI';
                return '<div><span class="font-weight-bold text-dark">' . htmlspecialchars($row->nama_perspektif) . '</span></div>' .
                       '<small class="text-muted"><i class="fas fa-link mr-1"></i>' . $countText . '</small>';
            })
            ->addColumn('badge_preview', function ($row) {
                $kode = strtoupper($row->kode ?? '');
                $style = match($kode) {
                    'FIN' => 'background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);',
                    'CUS' => 'background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25);',
                    'INT' => 'background: rgba(139, 92, 246, 0.1); color: #7c3aed; border: 1px solid rgba(139, 92, 246, 0.25);',
                    'LRN' => 'background: rgba(217, 119, 6, 0.1); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25);',
                    default => match($row->warna_badge) {
                        'success' => 'background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);',
                        'info'    => 'background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25);',
                        'warning' => 'background: rgba(217, 119, 6, 0.1); color: #b45309; border: 1px solid rgba(217, 119, 6, 0.25);',
                        'danger'  => 'background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25);',
                        default   => 'background: rgba(9, 75, 84, 0.1); color: #094b54; border: 1px solid rgba(9, 75, 84, 0.25);',
                    }
                };
                return '<span class="badge" style="' . $style . ' border-radius: 9999px; font-weight: 600; padding: 4px 10px;">' . htmlspecialchars($row->nama_perspektif) . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->is_active 
                    ? '<span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Aktif</span>'
                    : '<span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 9999px; font-weight: 600; padding: 4px 10px;">Nonaktif</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="d-flex justify-content-center align-items-center" style="gap: 5px;">';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'" title="Edit" style="border-radius: 6px; padding: 3px 8px; font-size: 0.8rem;"><i class="fas fa-pencil-alt"></i></button>';
                $btn .= '<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="'.$row->id.'" title="Hapus" style="border-radius: 6px; padding: 3px 8px; font-size: 0.8rem;"><i class="fas fa-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['nama_perspektif_fmt', 'badge_preview', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'            => 'required|string|max:20|unique:kpi_master_perspektifs,kode',
            'nama_perspektif' => 'required|string|max:100',
            'deskripsi'       => 'nullable|string',
            'warna_badge'     => 'required|string|max:30',
            'urutan'          => 'nullable|integer|min:1',
            'is_active'       => 'nullable|boolean',
        ]);

        $maxUrutan = KpiMasterPerspektif::max('urutan') ?? 0;

        $perspektif = KpiMasterPerspektif::create([
            'kode'            => strtoupper(trim($validated['kode'])),
            'nama_perspektif' => $validated['nama_perspektif'],
            'deskripsi'       => $validated['deskripsi'] ?? null,
            'warna_badge'     => $validated['warna_badge'],
            'urutan'          => $validated['urutan'] ?? ($maxUrutan + 1),
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return $this->successResponse($perspektif, 'Perspektif BSC berhasil ditambahkan');
    }

    public function show($id)
    {
        $perspektif = KpiMasterPerspektif::withCount('indikators')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data'   => $perspektif,
        ]);
    }

    public function update(Request $request, $id)
    {
        $perspektif = KpiMasterPerspektif::findOrFail($id);

        $validated = $request->validate([
            'kode'            => 'required|string|max:20|unique:kpi_master_perspektifs,kode,' . $id,
            'nama_perspektif' => 'required|string|max:100',
            'deskripsi'       => 'nullable|string',
            'warna_badge'     => 'required|string|max:30',
            'urutan'          => 'nullable|integer|min:1',
            'is_active'       => 'nullable|boolean',
        ]);

        $perspektif->update([
            'kode'            => strtoupper(trim($validated['kode'])),
            'nama_perspektif' => $validated['nama_perspektif'],
            'deskripsi'       => $validated['deskripsi'] ?? null,
            'warna_badge'     => $validated['warna_badge'],
            'urutan'          => $validated['urutan'] ?? $perspektif->urutan,
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return $this->successResponse($perspektif, 'Perspektif BSC berhasil diperbarui');
    }

    public function destroy($id)
    {
        $perspektif = KpiMasterPerspektif::findOrFail($id);

        if ($perspektif->indikators()->count() > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Perspektif ini tidak dapat dihapus karena sudah memiliki indikator kinerja yang terdaftar!',
            ], 422);
        }

        $perspektif->delete();
        return $this->successResponse(null, 'Perspektif BSC berhasil dihapus');
    }
}
