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
        return view('admin::kpi.perspektif.index', [
            'title'    => 'Master Perspektif Balanced Scorecard (BSC)',
            'menuIcon' => 'fas fa-layer-group',
        ]);
    }

    public function dataTable()
    {
        $query = KpiMasterPerspektif::withCount('indikators')->orderBy('urutan', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('badge_preview', function ($row) {
                return $row->badge_html;
            })
            ->addColumn('status_badge', function ($row) {
                return $row->is_active 
                    ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Aktif</span>'
                    : '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Non-Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group btn-group-sm" role="group">';
                $btn .= '<button type="button" class="btn btn-primary btn-edit" data-id="'.$row->id.'" title="Edit"><i class="fas fa-edit"></i></button>';
                $btn .= '<button type="button" class="btn btn-danger btn-delete" data-id="'.$row->id.'" title="Hapus"><i class="fas fa-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['badge_preview', 'status_badge', 'action'])
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
