<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MiddlewareController;
use Illuminate\Support\Facades\DB;
use App\Models\MasterKomponenPresensi;
use Modules\System\Models\MenuSidebar;
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
        $stats = [
            'total'     => MasterKomponenPresensi::count(),
            'aktif'     => MasterKomponenPresensi::where('is_active', 'Y')->count(),
            'kehadiran' => MasterKomponenPresensi::where('satuan', 'per_kehadiran')->count(),
            'periode'   => MasterKomponenPresensi::whereIn('satuan', ['per_bulan', 'per_hari'])->count(),
        ];

        $menuIcon = MenuSidebar::where('route', 'admin.master-komponen-presensi.index')->value('icon') ?: 'fas fa-money-bill-wave';

        return view('admin::master-data.komponen-presensi.index', [
            'title'    => 'Master Tarif & Komponen Presensi',
            'menuIcon' => $menuIcon,
            'stats'    => $stats,
        ]);
    }

    public function datatable()
    {
        $data = MasterKomponenPresensi::orderBy('nama_komponen', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('nama_komponen', function ($row) {
                $kode = $row->kode_komponen ? '<span class="badge badge-light text-muted border ml-1 font-weight-normal">' . e($row->kode_komponen) . '</span>' : '';
                return '<div class="font-weight-bold text-dark">' . e($row->nama_komponen) . $kode . '</div>';
            })
            ->addColumn('nominal_formatted', function ($row) {
                $satuanMap = [
                    'per_kehadiran' => 'per kehadiran',
                    'per_hari'      => 'per hari',
                    'per_bulan'     => 'per bulan',
                ];
                $satuanLabel = $satuanMap[$row->satuan] ?? str_replace('_', ' ', $row->satuan);
                return '<div><span class="font-weight-bold text-dark">Rp ' . number_format($row->nominal, 0, ',', '.') . '</span> <span class="text-muted text-xs">/ ' . e($satuanLabel) . '</span></div>';
            })
            ->addColumn('kategori_badge', function ($row) {
                $badges = [
                    'transport'           => '<span class="badge badge-pill badge-primary px-2 py-1 font-weight-normal">Transport</span>',
                    'makan'               => '<span class="badge badge-pill badge-warning px-2 py-1 font-weight-normal text-dark">Uang Makan</span>',
                    'tunjangan_kehadiran' => '<span class="badge badge-pill badge-info px-2 py-1 font-weight-normal">Tunjangan Kehadiran</span>',
                    'lainnya'             => '<span class="badge badge-pill badge-secondary px-2 py-1 font-weight-normal">Lainnya</span>',
                ];
                return $badges[$row->kategori] ?? '<span class="badge badge-pill badge-light px-2 py-1 font-weight-normal border">' . e($row->kategori) . '</span>';
            })
            ->editColumn('keterangan', function ($row) {
                return $row->keterangan ? '<span class="text-muted" style="font-size: 0.85rem;">' . e($row->keterangan) . '</span>' : '<span class="text-muted text-xs font-italic">-</span>';
            })
            ->addColumn('status', function ($row) {
                if ($row->is_active === 'Y') {
                    return '<span class="badge badge-pill badge-success px-2 py-1 font-weight-normal">Aktif</span>';
                }
                return '<span class="badge badge-pill badge-secondary px-2 py-1 font-weight-normal">Tidak Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button type="button" class="btn btn-xs btn-primary btn-modal mr-1" data-url="' . route('admin.master-komponen-presensi.edit', $row->id) . '" title="Edit Komponen"><i class="fas fa-edit"></i></button>';
                $btnToggle = '<button type="button" class="btn btn-xs ' . ($row->is_active === 'Y' ? 'btn-warning' : 'btn-success') . ' btn-delete" data-url="' . route('admin.master-komponen-presensi.destroy', $row->id) . '" data-name="' . htmlspecialchars($row->nama_komponen, ENT_QUOTES) . '" title="' . ($row->is_active === 'Y' ? 'Nonaktifkan' : 'Aktifkan') . '"><i class="fas fa-power-off"></i></button>';
                return '<div class="text-center">' . $btnEdit . $btnToggle . '</div>';
            })
            ->rawColumns(['nama_komponen', 'nominal_formatted', 'kategori_badge', 'keterangan', 'status', 'action'])
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
