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
        $this->registerPermissions('admin:pengembangan-sdm');
    }

    /**
     * Master Bidang Keilmuan
     */
    public function bidangIndex(Request $request)
    {
        $this->guard('view', 'admin:pengembangan-sdm');

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
        $this->guard('edit', 'admin:pengembangan-sdm');

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
