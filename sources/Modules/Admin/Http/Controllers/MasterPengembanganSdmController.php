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
        // Permission handling via explicit guard calls
    }

    /**
     * Master Bidang Keilmuan - Index View
     */
    public function bidangIndex(Request $request)
    {
        $this->guard('view', 'admin:master-bidang-keilmuan');

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
        $this->guard('create', 'admin:master-bidang-keilmuan');

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
        $this->guard('delete', 'admin:master-bidang-keilmuan');

        MasterBidangKeilmuan::findOrFail($id)->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Bidang keilmuan berhasil dihapus!']);
        }
        return redirect()->back()->with('success', 'Bidang keilmuan berhasil dihapus!');
    }

    /**
     * Master Sertifikasi Kompetensi - Index View
     */
    public function sertifikasiIndex(Request $request)
    {
        $this->guard('view', 'admin:master-sertifikasi');

        $stats = [
            'total'      => MasterSertifikasi::count(),
            'dosen'      => MasterSertifikasi::where('kategori_peserta', 'dosen')->count(),
            'tendik'     => MasterSertifikasi::where('kategori_peserta', 'tendik')->count(),
            'unit_count' => MasterSertifikasi::whereNotNull('unit_id')->distinct('unit_id')->count('unit_id'),
        ];

        $menuIcon = MenuSidebar::where('route', 'admin.master-sertifikasi.index')->value('icon') ?: 'fas fa-certificate';
        $unitList = MasterUnit::orderBy('nama_unit')->get();

        return view('admin::master-pengembangan.sertifikasi_index', [
            'title'    => 'Master Sertifikasi Kompetensi',
            'menuIcon' => $menuIcon,
            'stats'    => $stats,
            'unitList' => $unitList,
        ]);
    }

    /**
     * Master Sertifikasi Kompetensi - DataTables JSON
     */
    public function sertifikasiJson(Request $request)
    {
        $this->guard('view', 'admin:pengembangan-sdm');

        $query = MasterSertifikasi::with('unit')->withCount('pengembanganSertifikasis as peserta_count')->select('master_sertifikasis.*');

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori_peserta', $request->kategori);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('nama_sertifikasi', function ($row) {
                return '<span class="font-weight-bold text-dark">' . e($row->nama_sertifikasi) . '</span>';
            })
            ->editColumn('kategori_peserta', function ($row) {
                $kat = strtolower($row->kategori_peserta ?? 'umum');
                if ($kat === 'dosen') {
                    return '<span class="badge badge-pill px-3 py-1 font-weight-bold" style="background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 0.76rem;">Dosen</span>';
                } elseif ($kat === 'tendik') {
                    return '<span class="badge badge-pill px-3 py-1 font-weight-bold" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.76rem;">Tendik</span>';
                }
                return '<span class="badge badge-pill px-3 py-1 font-weight-bold" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-size: 0.76rem;">Umum</span>';
            })
            ->editColumn('lembaga_penerbit', function ($row) {
                if ($row->lembaga_penerbit) {
                    return '<span class="font-weight-500 text-dark">' . e($row->lembaga_penerbit) . '</span>';
                }
                return '<span class="text-muted font-italic">-</span>';
            })
            ->editColumn('unit_name', function ($row) {
                if ($row->unit) {
                    return '<span class="font-weight-500 text-dark">' . e($row->unit->nama_unit) . '</span>';
                }
                return '<span class="text-muted font-italic">Semua Unit / Umum</span>';
            })
            ->editColumn('peserta_count', function ($row) {
                $count = $row->peserta_count ?? 0;
                return '<span class="badge badge-pill px-3 py-1 font-weight-bold" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.76rem;">' . $count . ' Target</span>';
            })
            ->addColumn('action', function ($row) {
                $editBtn = '<button type="button" class="btn btn-primary btn-xs mr-1 btn-edit-sertifikasi" ' .
                    'data-id="' . $row->id . '" ' .
                    'data-nama="' . e($row->nama_sertifikasi) . '" ' .
                    'data-kategori="' . e($row->kategori_peserta ?? 'umum') . '" ' .
                    'data-lembaga="' . e($row->lembaga_penerbit ?? '') . '" ' .
                    'data-unit="' . e($row->unit_id ?? '') . '" ' .
                    'title="Ubah Sertifikasi"><i class="fas fa-edit mr-1"></i>Edit</button>';

                $deleteUrl = route('admin.master-sertifikasi.destroy', $row->id);
                $delBtn = '<button type="button" class="btn btn-danger btn-xs btn-delete-sertifikasi" ' .
                    'data-id="' . $row->id . '" ' .
                    'data-name="' . e($row->nama_sertifikasi) . '" ' .
                    'data-url="' . $deleteUrl . '" ' .
                    'title="Hapus Sertifikasi"><i class="fas fa-trash mr-1"></i>Hapus</button>';

                return '<div class="text-center text-nowrap">' . $editBtn . $delBtn . '</div>';
            })
            ->rawColumns(['nama_sertifikasi', 'kategori_peserta', 'lembaga_penerbit', 'unit_name', 'peserta_count', 'action'])
            ->make(true);
    }

    public function sertifikasiStore(Request $request)
    {
        $this->guard('create', 'admin:master-sertifikasi');

        $request->validate([
            'nama_sertifikasi'    => 'required|string|max:150',
            'kategori_peserta'    => 'nullable|in:dosen,tendik,umum',
            'lembaga_penerbit'    => 'nullable|string|max:100',
            'lembaga_sertifikasi' => 'nullable|string|max:100',
            'unit_id'             => 'nullable|uuid',
        ]);

        $lembaga = $request->lembaga_penerbit ?: $request->lembaga_sertifikasi;

        MasterSertifikasi::create([
            'nama_sertifikasi' => trim($request->nama_sertifikasi),
            'kategori_peserta' => $request->kategori_peserta ?? 'umum',
            'lembaga_penerbit' => $lembaga ? trim($lembaga) : null,
            'unit_id'          => $request->unit_id,
            'is_active'        => true,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Sertifikasi kompetensi berhasil ditambahkan!']);
        }
        return redirect()->back()->with('success', 'Sertifikasi kompetensi berhasil ditambahkan!');
    }

    public function sertifikasiUpdate(Request $request, $id)
    {
        $this->guard('edit', 'admin:pengembangan-sdm');

        $request->validate([
            'nama_sertifikasi'    => 'required|string|max:150',
            'kategori_peserta'    => 'nullable|in:dosen,tendik,umum',
            'lembaga_penerbit'    => 'nullable|string|max:100',
            'lembaga_sertifikasi' => 'nullable|string|max:100',
            'unit_id'             => 'nullable|uuid',
        ]);

        $sertifikasi = MasterSertifikasi::findOrFail($id);
        $lembaga = $request->lembaga_penerbit ?: $request->lembaga_sertifikasi;

        $sertifikasi->update([
            'nama_sertifikasi' => trim($request->nama_sertifikasi),
            'kategori_peserta' => $request->kategori_peserta ?? 'umum',
            'lembaga_penerbit' => $lembaga ? trim($lembaga) : null,
            'unit_id'          => $request->unit_id,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Sertifikasi kompetensi berhasil diperbarui!']);
        }
        return redirect()->back()->with('success', 'Sertifikasi kompetensi berhasil diperbarui!');
    }

    public function sertifikasiDestroy($id)
    {
        $this->guard('delete', 'admin:master-sertifikasi');

        MasterSertifikasi::findOrFail($id)->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Sertifikasi kompetensi berhasil dihapus!']);
        }
        return redirect()->back()->with('success', 'Sertifikasi kompetensi berhasil dihapus!');
    }

    /**
     * Master Periode Renstra Pengembangan SDM
     */
    public function periodeIndex(Request $request)
    {
        $this->guard('view', 'admin:master-periode-pengembangan');

        if ($request->ajax()) {
            $data = MasterPeriodePengembangan::withCount('pesertas')->orderBy('tahun_mulai', 'desc');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('rentang_tahun', fn($row) => $row->tahun_mulai . ' - ' . $row->tahun_selesai)
                ->editColumn('target_persen_doktor', fn($row) => number_format($row->target_persen_doktor, 2) . '%')
                ->editColumn('is_active', function ($row) {
                    if ($row->is_active) {
                        return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Aktif</span>';
                    }
                    return '<span class="badge badge-secondary px-2 py-1">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($row) {
                    $activeBtn = !$row->is_active 
                        ? '<button class="btn btn-xs btn-outline-success mr-1 btn-set-active" data-id="' . $row->id . '" title="Set sebagai Periode Aktif"><i class="fas fa-check"></i> Aktifkan</button>' 
                        : '';
                    $editBtn = '<button class="btn btn-xs btn-info mr-1 btn-edit-periode" data-id="' . $row->id . '" data-nama="' . htmlspecialchars($row->nama_periode) . '" data-mulai="' . $row->tahun_mulai . '" data-selesai="' . $row->tahun_selesai . '" data-target="' . $row->target_persen_doktor . '" data-active="' . ($row->is_active ? '1' : '0') . '"><i class="fas fa-edit"></i> Edit</button>';
                    $delBtn = '<button class="btn btn-xs btn-danger btn-delete-periode" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>';
                    return $activeBtn . $editBtn . $delBtn;
                })
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        $periodeList = MasterPeriodePengembangan::withCount('pesertas')->orderBy('tahun_mulai', 'desc')->get();
        return view('admin::master-pengembangan.periode_index', [
            'title' => 'Master Periode Pengembangan SDM (Renstra)',
            'periodeList' => $periodeList,
        ]);
    }

    public function periodeStore(Request $request)
    {
        $this->guard('create', 'admin:master-periode-pengembangan');

        $request->validate([
            'nama_periode' => 'required|string|max:150',
            'tahun_mulai' => 'required|integer|min:2000|max:2100',
            'tahun_selesai' => 'required|integer|gte:tahun_mulai|max:2100',
            'target_persen_doktor' => 'required|numeric|min:0|max:100',
            'is_active' => 'nullable',
        ]);

        $isActive = $request->boolean('is_active');
        if ($isActive) {
            MasterPeriodePengembangan::query()->update(['is_active' => false]);
        }

        MasterPeriodePengembangan::create([
            'nama_periode' => $request->nama_periode,
            'tahun_mulai' => $request->tahun_mulai,
            'tahun_selesai' => $request->tahun_selesai,
            'target_persen_doktor' => $request->target_persen_doktor,
            'is_active' => $isActive,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Periode pengembangan SDM berhasil ditambahkan!']);
        }
        return redirect()->back()->with('success', 'Periode pengembangan SDM berhasil ditambahkan!');
    }

    public function periodeUpdate(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-periode-pengembangan');

        $periode = MasterPeriodePengembangan::findOrFail($id);

        $request->validate([
            'nama_periode' => 'required|string|max:150',
            'tahun_mulai' => 'required|integer|min:2000|max:2100',
            'tahun_selesai' => 'required|integer|gte:tahun_mulai|max:2100',
            'target_persen_doktor' => 'required|numeric|min:0|max:100',
            'is_active' => 'nullable',
        ]);

        $isActive = $request->boolean('is_active');
        if ($isActive) {
            MasterPeriodePengembangan::where('id', '!=', $id)->update(['is_active' => false]);
        }

        $periode->update([
            'nama_periode' => $request->nama_periode,
            'tahun_mulai' => $request->tahun_mulai,
            'tahun_selesai' => $request->tahun_selesai,
            'target_persen_doktor' => $request->target_persen_doktor,
            'is_active' => $isActive,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Periode pengembangan SDM berhasil diperbarui!']);
        }
        return redirect()->back()->with('success', 'Periode pengembangan SDM berhasil diperbarui!');
    }

    public function periodeSetActive($id)
    {
        $this->guard('edit', 'admin:master-periode-pengembangan');

        MasterPeriodePengembangan::query()->update(['is_active' => false]);
        $periode = MasterPeriodePengembangan::findOrFail($id);
        $periode->update(['is_active' => true]);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Periode "' . $periode->nama_periode . '" berhasil diset sebagai periode aktif!']);
        }
        return redirect()->back()->with('success', 'Periode berhasil diset sebagai periode aktif!');
    }

    public function periodeDestroy($id)
    {
        $this->guard('delete', 'admin:master-periode-pengembangan');

        $periode = MasterPeriodePengembangan::withCount('pesertas')->findOrFail($id);
        if ($periode->pesertas_count > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus periode ini karena memiliki ' . $periode->pesertas_count . ' data peserta roadmap terkait.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Tidak dapat menghapus periode ini karena memiliki data peserta roadmap terkait.');
        }

        $periode->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Periode berhasil dihapus!']);
        }
        return redirect()->back()->with('success', 'Periode berhasil dihapus!');
    }
}
