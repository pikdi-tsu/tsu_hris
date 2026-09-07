<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MiddlewareController;
use Illuminate\Support\Facades\DB;
use App\Models\MasterKomponenPresensi;
use App\Services\TsuErrorHandlerService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Traits\ApiResponseTrait;

class MasterKomponenPresensiController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware('auth');
        $this->registerPermissions('admin:master-komponen-presensi');
    }

    public function index()
    {
        return view('admin::master-data.komponen-presensi.index', ['title' => 'Master Tarif & Komponen Presensi']);
    }

    public function datatable()
    {
        $data = MasterKomponenPresensi::orderBy('nama_komponen', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nominal_formatted', function ($row) {
                return '<strong>Rp ' . number_format($row->nominal, 0, ',', '.') . '</strong> <small class="text-muted">/ ' . str_replace('_', ' ', $row->satuan) . '</small>';
            })
            ->addColumn('kategori_badge', function ($row) {
                $badges = [
                    'transport' => '<span class="badge badge-primary"><i class="fas fa-bus mr-1"></i> Transport</span>',
                    'makan' => '<span class="badge badge-warning"><i class="fas fa-utensils mr-1"></i> Uang Makan</span>',
                    'tunjangan_kehadiran' => '<span class="badge badge-info"><i class="fas fa-award mr-1"></i> Tunjangan Kehadiran</span>',
                    'lainnya' => '<span class="badge badge-secondary">Lainnya</span>',
                ];
                return $badges[$row->kategori] ?? '<span class="badge badge-light">' . $row->kategori . '</span>';
            })
            ->addColumn('status', function ($row) {
                if ($row->is_active === 'Y') {
                    return '<span class="badge badge-success">Aktif</span>';
                }
                return '<span class="badge badge-secondary">Tidak Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button type="button" class="btn btn-xs btn-primary btn-modal mr-1" data-url="' . route('admin.master-komponen-presensi.edit', $row->id) . '" title="Edit"><i class="fas fa-edit"></i></button>';
                $btnToggle = '<button type="button" class="btn btn-xs ' . ($row->is_active === 'Y' ? 'btn-warning' : 'btn-success') . ' btn-delete" data-url="' . route('admin.master-komponen-presensi.destroy', $row->id) . '" data-name="' . $row->nama_komponen . '" title="' . ($row->is_active === 'Y' ? 'Nonaktifkan' : 'Aktifkan') . '"><i class="fas fa-power-off"></i></button>';
                return '<div class="text-center">' . $btnEdit . $btnToggle . '</div>';
            })
            ->rawColumns(['nominal_formatted', 'kategori_badge', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $kategoriList = MasterKomponenPresensi::getKategoriList();
        return view('admin::master-data.komponen-presensi.create_modal', compact('kategoriList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_komponen' => 'required|string|max:255',
            'kode_komponen' => 'nullable|string|max:50',
            'kategori' => 'required|in:transport,makan,tunjangan_kehadiran,lainnya',
            'nominal' => 'required|numeric|min:0',
            'satuan' => 'required|in:per_kehadiran,per_hari,per_bulan',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            MasterKomponenPresensi::create([
                'id' => Str::uuid()->toString(),
                'nama_komponen' => $request->nama_komponen,
                'kode_komponen' => $request->kode_komponen ? strtoupper($request->kode_komponen) : Str::slug($request->nama_komponen),
                'kategori' => $request->kategori,
                'nominal' => $request->nominal,
                'satuan' => $request->satuan,
                'keterangan' => $request->keterangan,
                'is_active' => 'Y',
            ]);

            DB::commit();
            return $this->sendSuccess('Komponen Presensi berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_KOMP_STORE]', 'Gagal menambah komponen presensi.', 'Gagal Store Komponen');
        }
    }

    public function edit($id)
    {
        $komponen = MasterKomponenPresensi::findOrFail($id);
        $kategoriList = MasterKomponenPresensi::getKategoriList();
        return view('admin::master-data.komponen-presensi.edit_modal', compact('komponen', 'kategoriList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_komponen' => 'required|string|max:255',
            'kode_komponen' => 'nullable|string|max:50',
            'kategori' => 'required|in:transport,makan,tunjangan_kehadiran,lainnya',
            'nominal' => 'required|numeric|min:0',
            'satuan' => 'required|in:per_kehadiran,per_hari,per_bulan',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $komponen = MasterKomponenPresensi::findOrFail($id);
            $komponen->update([
                'nama_komponen' => $request->nama_komponen,
                'kode_komponen' => $request->kode_komponen ? strtoupper($request->kode_komponen) : $komponen->kode_komponen,
                'kategori' => $request->kategori,
                'nominal' => $request->nominal,
                'satuan' => $request->satuan,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();
            return $this->sendSuccess('Komponen Presensi berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_KOMP_UPDATE]', 'Gagal memperbarui komponen presensi.', 'Gagal Update Komponen');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $komponen = MasterKomponenPresensi::findOrFail($id);
            $newStatus = $komponen->is_active === 'Y' ? 'N' : 'Y';
            $komponen->is_active = $newStatus;
            $komponen->save();

            DB::commit();
            return $this->sendSuccess('Status Komponen berhasil diubah menjadi ' . ($newStatus === 'Y' ? 'Aktif' : 'Tidak Aktif'));
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_KOMP_DESTROY]', 'Gagal mengubah status komponen.', 'Gagal Hapus Komponen');
        }
    }
}
