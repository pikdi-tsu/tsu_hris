<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\MasterBidangKeilmuan;
use App\Models\MasterPeriodePengembangan;
use App\Models\MasterSertifikasi;
use App\Models\MasterUnit;
use Modules\System\Models\MenuSidebar;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MasterPengembanganSdmController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:pengembangan-sdm');
    }

    /**
     * Master Bidang Keilmuan - Index View
     */
    public function bidangIndex(Request $request)
    {
        $this->guard('view', 'admin:pengembangan-sdm');

        $stats = [
            'total'      => MasterBidangKeilmuan::count(),
            'dosen'      => MasterBidangKeilmuan::where('kategori', 'dosen')->count(),
            'tendik'     => MasterBidangKeilmuan::where('kategori', 'tendik')->count(),
            'unit_count' => MasterBidangKeilmuan::whereNotNull('unit_id')->distinct('unit_id')->count('unit_id'),
        ];

        $menuIcon = MenuSidebar::where('route', 'admin.master-bidang-keilmuan.index')->value('icon') ?: 'fas fa-brain';
        $unitList = MasterUnit::orderBy('nama_unit')->get();

        return view('admin::master-pengembangan.bidang_index', [
            'title'    => 'Master Bidang Keilmuan',
            'menuIcon' => $menuIcon,
            'stats'    => $stats,
            'unitList' => $unitList,
        ]);
    }

    /**
     * Master Bidang Keilmuan - DataTables JSON
     */
    public function bidangJson(Request $request)
    {
        $this->guard('view', 'admin:pengembangan-sdm');

        $query = MasterBidangKeilmuan::with('unit')->withCount('pesertas')->select('master_bidang_keilmuans.*');

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('nama_bidang', function ($row) {
                return '<span class="font-weight-bold text-dark">' . e($row->nama_bidang) . '</span>';
            })
            ->editColumn('kode_bidang', function ($row) {
                if (!$row->kode_bidang) {
                    return '<span class="text-muted font-italic">-</span>';
                }
                return '<span class="badge px-2 py-1 font-weight-bold" style="background-color: #f1f5f9; color: #094b54; border: 1px solid #cbd5e1; border-radius: 6px; font-family: monospace; font-size: 0.8rem;">' . e($row->kode_bidang) . '</span>';
            })
            ->editColumn('kategori', function ($row) {
                $kat = strtolower($row->kategori ?? 'umum');
                if ($kat === 'dosen') {
                    return '<span class="badge badge-pill px-3 py-1 font-weight-bold" style="background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 0.76rem;">Dosen</span>';
                } elseif ($kat === 'tendik') {
                    return '<span class="badge badge-pill px-3 py-1 font-weight-bold" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.76rem;">Tendik</span>';
                }
                return '<span class="badge badge-pill px-3 py-1 font-weight-bold" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-size: 0.76rem;">Umum</span>';
            })
            ->editColumn('unit_name', function ($row) {
                if ($row->unit) {
                    return '<span class="font-weight-500 text-dark">' . e($row->unit->nama_unit) . '</span>';
                }
                return '<span class="text-muted font-italic">Semua Unit / Umum</span>';
            })
            ->editColumn('peserta_count', function ($row) {
                $count = $row->pesertas_count ?? 0;
                return '<span class="badge badge-pill px-3 py-1 font-weight-bold" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.76rem;">' . $count . ' Peserta</span>';
            })
            ->addColumn('action', function ($row) {
                $editBtn = '<button type="button" class="btn btn-primary btn-xs mr-1 btn-edit-bidang" ' .
                    'data-id="' . $row->id . '" ' .
                    'data-nama="' . e($row->nama_bidang) . '" ' .
                    'data-kode="' . e($row->kode_bidang ?? '') . '" ' .
                    'data-kategori="' . e($row->kategori ?? 'dosen') . '" ' .
                    'data-unit="' . e($row->unit_id ?? '') . '" ' .
                    'title="Ubah Bidang Keilmuan"><i class="fas fa-edit mr-1"></i>Edit</button>';

                $deleteUrl = route('admin.master-bidang-keilmuan.destroy', $row->id);
                $delBtn = '<button type="button" class="btn btn-danger btn-xs btn-delete-bidang" ' .
                    'data-id="' . $row->id . '" ' .
                    'data-name="' . e($row->nama_bidang) . '" ' .
                    'data-url="' . $deleteUrl . '" ' .
                    'title="Hapus Bidang Keilmuan"><i class="fas fa-trash mr-1"></i>Hapus</button>';

                return '<div class="text-center text-nowrap">' . $editBtn . $delBtn . '</div>';
            })
            ->rawColumns(['nama_bidang', 'kode_bidang', 'kategori', 'unit_name', 'peserta_count', 'action'])
            ->make(true);
    }

    public function bidangStore(Request $request)
    {
        $this->guard('edit', 'admin:pengembangan-sdm');

        $request->validate([
            'kode_bidang' => 'nullable|string|max:20',
            'nama_bidang' => 'required|string|max:100',
            'kategori'    => 'nullable|in:dosen,tendik,umum',
            'unit_id'     => 'nullable|uuid',
        ]);

        MasterBidangKeilmuan::create([
            'kode_bidang' => $request->kode_bidang ? strtoupper(trim($request->kode_bidang)) : null,
            'nama_bidang' => trim($request->nama_bidang),
            'kategori'    => $request->kategori ?? 'dosen',
            'unit_id'     => $request->unit_id,
            'is_active'   => true,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Bidang keilmuan berhasil ditambahkan!']);
        }
        return redirect()->back()->with('success', 'Bidang keilmuan berhasil ditambahkan!');
    }

    public function bidangUpdate(Request $request, $id)
    {
        $this->guard('edit', 'admin:pengembangan-sdm');

        $request->validate([
            'kode_bidang' => 'nullable|string|max:20',
            'nama_bidang' => 'required|string|max:100',
            'kategori'    => 'nullable|in:dosen,tendik,umum',
            'unit_id'     => 'nullable|uuid',
        ]);

        $bidang = MasterBidangKeilmuan::findOrFail($id);
        $bidang->update([
            'kode_bidang' => $request->kode_bidang ? strtoupper(trim($request->kode_bidang)) : null,
            'nama_bidang' => trim($request->nama_bidang),
            'kategori'    => $request->kategori ?? 'dosen',
            'unit_id'     => $request->unit_id,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Bidang keilmuan berhasil diperbarui!']);
        }
        return redirect()->back()->with('success', 'Bidang keilmuan berhasil diperbarui!');
    }

    public function bidangDestroy($id)
    {
        $this->guard('delete', 'admin:pengembangan-sdm');

        MasterBidangKeilmuan::findOrFail($id)->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Bidang keilmuan berhasil dihapus!']);
        }
        return redirect()->back()->with('success', 'Bidang keilmuan berhasil dihapus!');
    }

    /**
     * Master Sertifikasi Kompetensi
     */
    public function sertifikasiIndex(Request $request)
    {
        $this->guard('view', 'admin:pengembangan-sdm');

        if ($request->ajax()) {
            $data = MasterSertifikasi::with('unit')->select('master_sertifikasis.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('unit_name', fn($row) => $row->unit ? $row->unit->nama_unit : 'Semua / Umum')
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-xs btn-danger btn-delete-sert" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $unitList = MasterUnit::orderBy('nama_unit')->get();
        $sertifikasiList = MasterSertifikasi::with('unit')->withCount('pengembanganSertifikasis as peserta_count')->orderBy('nama_sertifikasi')->get();
        return view('admin::master-pengembangan.sertifikasi_index', [
            'title' => 'Master Sertifikasi Kompetensi',
            'unitList' => $unitList,
            'sertifikasiList' => $sertifikasiList,
        ]);
    }

    public function sertifikasiStore(Request $request)
    {
        $this->guard('edit', 'admin:pengembangan-sdm');

        $request->validate([
            'nama_sertifikasi' => 'required|string|max:150',
            'kategori_peserta' => 'nullable|in:dosen,tendik,umum',
            'lembaga_sertifikasi' => 'nullable|string|max:100',
            'unit_id' => 'nullable|uuid',
        ]);

        MasterSertifikasi::create([
            'nama_sertifikasi' => $request->nama_sertifikasi,
            'kategori_peserta' => $request->kategori_peserta ?? 'umum',
            'lembaga_sertifikasi' => $request->lembaga_sertifikasi,
            'unit_id' => $request->unit_id,
            'is_active' => true,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Sertifikasi berhasil ditambahkan!']);
        }
        return redirect()->back()->with('success', 'Sertifikasi berhasil ditambahkan!');
    }

    public function sertifikasiDestroy($id)
    {
        $this->guard('delete', 'admin:pengembangan-sdm');

        MasterSertifikasi::findOrFail($id)->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Sertifikasi berhasil dihapus!']);
        }
        return redirect()->back()->with('success', 'Sertifikasi berhasil dihapus!');
    }
}
