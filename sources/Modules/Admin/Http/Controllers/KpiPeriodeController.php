<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\KpiPeriode;
use App\Traits\ApiResponseTrait;

class KpiPeriodeController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:kpi');
    }

    public function index()
    {
        return view('admin::kpi.periode.index', [
            'title'    => 'Master Periode Penilaian KPI',
            'menuIcon' => 'fas fa-calendar-alt',
        ]);
    }

    public function dataTable()
    {
        $query = KpiPeriode::withCount('unitIndikators')->orderBy('tahun', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('status_badge', function ($row) {
                return $row->status_badge;
            })
            ->addColumn('kunci_badge', function ($row) {
                return $row->kunci_badge;
            })
            ->addColumn('rentang_tanggal', function ($row) {
                return ($row->tanggal_mulai ? date('d M Y', strtotime($row->tanggal_mulai)) : '-') 
                    . ' s/d ' . 
                    ($row->tanggal_selesai ? date('d M Y', strtotime($row->tanggal_selesai)) : '-');
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group btn-group-sm" role="group">';
                
                // Toggle Aktif
                if (!$row->is_active) {
                    $btn .= '<button type="button" class="btn btn-outline-success btn-toggle-active" data-id="'.$row->id.'" title="Setel Sebagai Periode Aktif"><i class="fas fa-check-circle"></i></button>';
                }

                // Toggle Kunci
                if ($row->is_locked) {
                    $btn .= '<button type="button" class="btn btn-outline-warning btn-toggle-lock" data-id="'.$row->id.'" title="Buka Kunci Pengisian"><i class="fas fa-lock-open"></i></button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-outline-secondary btn-toggle-lock" data-id="'.$row->id.'" title="Kunci Pengisian"><i class="fas fa-lock"></i></button>';
                }

                $btn .= '<button type="button" class="btn btn-primary btn-edit" data-id="'.$row->id.'" title="Edit"><i class="fas fa-edit"></i></button>';
                $btn .= '<button type="button" class="btn btn-danger btn-delete" data-id="'.$row->id.'" title="Hapus"><i class="fas fa-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status_badge', 'kunci_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun'           => 'required|integer|min:2020|max:2050',
            'nama_periode'    => 'required|string|max:100',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'is_active'       => 'nullable|boolean',
            'keterangan'      => 'nullable|string',
        ]);

        $isActive = $request->boolean('is_active');
        if ($isActive) {
            KpiPeriode::query()->update(['is_active' => 0]);
        }

        $periode = KpiPeriode::create([
            'tahun'           => $validated['tahun'],
            'nama_periode'    => $validated['nama_periode'],
            'tanggal_mulai'   => $validated['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'is_active'       => $isActive ? 1 : 0,
            'is_locked'       => 0,
            'keterangan'      => $validated['keterangan'] ?? null,
        ]);

        return $this->successResponse($periode, 'Periode KPI berhasil ditambahkan');
    }

    public function show($id)
    {
        $periode = KpiPeriode::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data'   => $periode,
        ]);
    }

    public function update(Request $request, $id)
    {
        $periode = KpiPeriode::findOrFail($id);

        $validated = $request->validate([
            'tahun'           => 'required|integer|min:2020|max:2050',
            'nama_periode'    => 'required|string|max:100',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'is_active'       => 'nullable|boolean',
            'keterangan'      => 'nullable|string',
        ]);

        $isActive = $request->boolean('is_active');
        if ($isActive && !$periode->is_active) {
            KpiPeriode::query()->update(['is_active' => 0]);
        }

        $periode->update([
            'tahun'           => $validated['tahun'],
            'nama_periode'    => $validated['nama_periode'],
            'tanggal_mulai'   => $validated['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'is_active'       => $isActive ? 1 : 0,
            'keterangan'      => $validated['keterangan'] ?? null,
        ]);

        return $this->successResponse($periode, 'Periode KPI berhasil diperbarui');
    }

    public function destroy($id)
    {
        $periode = KpiPeriode::findOrFail($id);

        if ($periode->unitIndikators()->count() > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Periode tidak dapat dihapus karena sudah memiliki indikator kinerja yang tercatat!',
            ], 422);
        }

        $periode->delete();
        return $this->successResponse(null, 'Periode KPI berhasil dihapus');
    }

    public function toggleAktif($id)
    {
        KpiPeriode::query()->update(['is_active' => 0]);
        $periode = KpiPeriode::findOrFail($id);
        $periode->update(['is_active' => 1]);

        return $this->successResponse($periode, "Periode {$periode->nama_periode} telah ditetapkan sebagai periode aktif");
    }

    public function toggleKunci($id)
    {
        $periode = KpiPeriode::findOrFail($id);
        $periode->update(['is_locked' => !$periode->is_locked]);

        $statusStr = $periode->is_locked ? 'dikunci (input nilai ditutup)' : 'dibuka kembali (input nilai diizinkan)';
        return $this->successResponse($periode, "Periode {$periode->nama_periode} berhasil {$statusStr}");
    }
}
