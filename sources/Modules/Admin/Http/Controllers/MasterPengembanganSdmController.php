<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\MasterBidangKeilmuan;
use App\Models\MasterPeriodePengembangan;
use App\Models\MasterSertifikasi;
use App\Models\MasterUnit;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MasterPengembanganSdmController extends MiddlewareController
{
    public function __construct()
    {
        // Permission handling via explicit guard calls
    }

    /**
     * Master Bidang Keilmuan
     */
    public function bidangIndex(Request $request)
    {
        $this->guard('view', 'admin:master-bidang-keilmuan');

        if ($request->ajax()) {
            $data = MasterBidangKeilmuan::with('unit')->select('master_bidang_keilmuans.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('unit_name', fn($row) => $row->unit ? $row->unit->nama_unit : 'Semua / Umum')
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-xs btn-danger btn-delete-bidang" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $unitList = MasterUnit::orderBy('nama_unit')->get();
        $bidangList = MasterBidangKeilmuan::with('unit')->withCount('pesertas')->orderBy('nama_bidang')->get();
        return view('admin::master-pengembangan.bidang_index', [
            'title' => 'Master Bidang Keilmuan & Kepakaran',
            'unitList' => $unitList,
            'bidangList' => $bidangList,
        ]);
    }

    public function bidangStore(Request $request)
    {
        $this->guard('create', 'admin:master-bidang-keilmuan');

        $request->validate([
            'kode_bidang' => 'nullable|string|max:20',
            'nama_bidang' => 'required|string|max:100',
            'kategori' => 'nullable|in:dosen,tendik,umum',
            'unit_id' => 'nullable|uuid',
        ]);

        MasterBidangKeilmuan::create([
            'kode_bidang' => $request->kode_bidang ? strtoupper($request->kode_bidang) : null,
            'nama_bidang' => $request->nama_bidang,
            'kategori' => $request->kategori ?? 'dosen',
            'unit_id' => $request->unit_id,
            'is_active' => true,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Bidang keilmuan berhasil ditambahkan!']);
        }
        return redirect()->back()->with('success', 'Bidang keilmuan berhasil ditambahkan!');
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
     * Master Sertifikasi Kompetensi
     */
    public function sertifikasiIndex(Request $request)
    {
        $this->guard('view', 'admin:master-sertifikasi');

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
        $this->guard('create', 'admin:master-sertifikasi');

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
        $this->guard('delete', 'admin:master-sertifikasi');

        MasterSertifikasi::findOrFail($id)->delete();
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Sertifikasi berhasil dihapus!']);
        }
        return redirect()->back()->with('success', 'Sertifikasi berhasil dihapus!');
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
