<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MiddlewareController;
use Illuminate\Support\Facades\DB;
use App\Models\MasterGajiPokok;
use Modules\System\Models\MenuSidebar;
use App\Services\TsuErrorHandlerService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Traits\ApiResponseTrait;

class MasterGajiPokokController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware('auth');
        $this->registerPermissions('admin:master-gaji-pokok');
    }

    public function index()
    {
        $stats = [
            'total'     => MasterGajiPokok::count(),
            'gol_1_2'   => MasterGajiPokok::where(function($q) {
                $q->where('golongan', 'like', 'I/%')->orWhere('golongan', 'like', 'II/%');
            })->count(),
            'gol_3_4'   => MasterGajiPokok::where(function($q) {
                $q->where('golongan', 'like', 'III/%')->orWhere('golongan', 'like', 'IV/%');
            })->count(),
            'avg_gapok' => round(MasterGajiPokok::avg('gaji_pokok_100') ?? 0),
        ];

        $menuIcon = MenuSidebar::where('route', 'admin.master-gaji-pokok.index')->value('icon') ?: 'fas fa-money-check-alt';

        return view('admin::master-data.gaji-pokok.index', [
            'title'    => 'Master Matriks Gaji Pokok Pegawai',
            'menuIcon' => $menuIcon,
            'stats'    => $stats,
        ]);
    }

    public function datatable()
    {
        $data = MasterGajiPokok::orderByRaw("
            CASE 
                WHEN golongan LIKE 'I/%' THEN 1
                WHEN golongan LIKE 'II/%' THEN 2
                WHEN golongan LIKE 'III/%' THEN 3
                WHEN golongan LIKE 'IV/%' THEN 4
                ELSE 5
            END, golongan ASC
        ");

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('golongan_badge', function ($row) {
                return '<span class="badge badge-pill badge-primary px-3 py-1 font-weight-bold" style="font-size: 0.88rem;">' . e($row->golongan) . '</span>';
            })
            ->addColumn('gapok_100_formatted', function ($row) {
                return '<span class="font-weight-bold" style="color: #047857; font-size: 0.92rem;">Rp ' . number_format($row->gaji_pokok_100, 0, ',', '.') . '</span>';
            })
            ->addColumn('gapok_80_formatted', function ($row) {
                return '<span class="font-weight-bold text-dark" style="font-size: 0.9rem;">Rp ' . number_format($row->gaji_pokok_80, 0, ',', '.') . '</span>';
            })
            ->addColumn('berkala_formatted', function ($row) {
                $html = '<div class="text-muted" style="font-size: 0.78rem; line-height: 1.45;">';
                $html .= '<div><span class="font-weight-bold text-dark">2 Thn:</span> Rp ' . number_format($row->tahun_2, 0, ',', '.') . ' &nbsp;|&nbsp; <span class="font-weight-bold text-dark">4 Thn:</span> Rp ' . number_format($row->tahun_4, 0, ',', '.') . '</div>';
                $html .= '<div><span class="font-weight-bold text-dark">6 Thn:</span> Rp ' . number_format($row->tahun_6, 0, ',', '.') . ' &nbsp;|&nbsp; <span class="font-weight-bold text-dark">8 Thn:</span> Rp ' . number_format($row->tahun_8, 0, ',', '.') . '</div>';
                $html .= '<div><span class="font-weight-bold text-dark">10 Thn:</span> Rp ' . number_format($row->tahun_10, 0, ',', '.') . '</div>';
                $html .= '</div>';
                return $html;
            })
            ->addColumn('keterangan_display', function ($row) {
                return $row->keterangan ? '<span class="text-muted" style="font-size: 0.83rem;">' . nl2br(e($row->keterangan)) . '</span>' : '<span class="text-muted text-xs font-italic">-</span>';
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button type="button" class="btn btn-xs btn-primary btn-modal mr-1" data-url="' . route('admin.master-gaji-pokok.edit', $row->id) . '" title="Edit Matriks"><i class="fas fa-edit"></i> Edit</button>';
                $btnDelete = '<button type="button" class="btn btn-xs btn-danger btn-delete" data-url="' . route('admin.master-gaji-pokok.destroy', $row->id) . '" data-name="Golongan ' . htmlspecialchars($row->golongan, ENT_QUOTES) . '" title="Hapus Golongan"><i class="fas fa-trash"></i></button>';
                return '<div class="text-center text-nowrap">' . $btnEdit . $btnDelete . '</div>';
            })
            ->rawColumns(['golongan_badge', 'gapok_100_formatted', 'gapok_80_formatted', 'berkala_formatted', 'keterangan_display', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin::master-data.gaji-pokok.create_modal');
    }

    public function store(Request $request)
    {
        $request->validate([
            'golongan'       => 'required|string|max:20|unique:master_gaji_pokoks,golongan',
            'gaji_pokok_100' => 'required|numeric|min:0',
            'gaji_pokok_80'  => 'nullable|numeric|min:0',
            'tahun_2'        => 'nullable|numeric|min:0',
            'tahun_4'        => 'nullable|numeric|min:0',
            'tahun_6'        => 'nullable|numeric|min:0',
            'tahun_8'        => 'nullable|numeric|min:0',
            'tahun_10'       => 'nullable|numeric|min:0',
            'keterangan'     => 'nullable|string',
        ], [
            'golongan.required' => 'Golongan wajib diisi.',
            'golongan.unique'   => 'Golongan tersebut sudah terdaftar.',
            'gaji_pokok_100.required' => 'Gaji Pokok 100% wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $gapok100 = floatval($request->gaji_pokok_100);
            $gapok80  = $request->filled('gaji_pokok_80') ? floatval($request->gaji_pokok_80) : round($gapok100 * 0.8, 2);

            MasterGajiPokok::create([
                'id'             => Str::uuid()->toString(),
                'golongan'       => trim($request->golongan),
                'gaji_pokok_100' => $gapok100,
                'gaji_pokok_80'  => $gapok80,
                'tahun_2'        => $request->filled('tahun_2') ? floatval($request->tahun_2) : round($gapok100 * 1.1, 2),
                'tahun_4'        => $request->filled('tahun_4') ? floatval($request->tahun_4) : round($gapok100 * 1.21, 2),
                'tahun_6'        => $request->filled('tahun_6') ? floatval($request->tahun_6) : round($gapok100 * 1.331, 2),
                'tahun_8'        => $request->filled('tahun_8') ? floatval($request->tahun_8) : round($gapok100 * 1.4641, 2),
                'tahun_10'       => $request->filled('tahun_10') ? floatval($request->tahun_10) : round($gapok100 * 1.6105, 2),
                'keterangan'     => $request->keterangan,
            ]);

            DB::commit();
            return $this->sendSuccess('Master Gaji Pokok untuk Golongan ' . $request->golongan . ' berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_GAPOK_STORE]', 'Gagal menambah data master gaji pokok.', 'Gagal Store Gaji Pokok');
        }
    }

    public function edit($id)
    {
        $gapok = MasterGajiPokok::findOrFail($id);
        return view('admin::master-data.gaji-pokok.edit_modal', compact('gapok'));
    }

    public function update(Request $request, $id)
    {
        $gapok = MasterGajiPokok::findOrFail($id);

        $request->validate([
            'golongan'       => 'required|string|max:20|unique:master_gaji_pokoks,golongan,' . $id,
            'gaji_pokok_100' => 'required|numeric|min:0',
            'gaji_pokok_80'  => 'nullable|numeric|min:0',
            'tahun_2'        => 'nullable|numeric|min:0',
            'tahun_4'        => 'nullable|numeric|min:0',
            'tahun_6'        => 'nullable|numeric|min:0',
            'tahun_8'        => 'nullable|numeric|min:0',
            'tahun_10'       => 'nullable|numeric|min:0',
            'keterangan'     => 'nullable|string',
        ], [
            'golongan.required' => 'Golongan wajib diisi.',
            'golongan.unique'   => 'Golongan tersebut sudah digunakan.',
            'gaji_pokok_100.required' => 'Gaji Pokok 100% wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $gapok100 = floatval($request->gaji_pokok_100);
            $gapok80  = $request->filled('gaji_pokok_80') ? floatval($request->gaji_pokok_80) : round($gapok100 * 0.8, 2);

            $gapok->update([
                'golongan'       => trim($request->golongan),
                'gaji_pokok_100' => $gapok100,
                'gaji_pokok_80'  => $gapok80,
                'tahun_2'        => $request->filled('tahun_2') ? floatval($request->tahun_2) : $gapok->tahun_2,
                'tahun_4'        => $request->filled('tahun_4') ? floatval($request->tahun_4) : $gapok->tahun_4,
                'tahun_6'        => $request->filled('tahun_6') ? floatval($request->tahun_6) : $gapok->tahun_6,
                'tahun_8'        => $request->filled('tahun_8') ? floatval($request->tahun_8) : $gapok->tahun_8,
                'tahun_10'       => $request->filled('tahun_10') ? floatval($request->tahun_10) : $gapok->tahun_10,
                'keterangan'     => $request->keterangan,
            ]);

            DB::commit();
            return $this->sendSuccess('Master Gaji Pokok untuk Golongan ' . $gapok->golongan . ' berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_GAPOK_UPDATE]', 'Gagal memperbarui data master gaji pokok.', 'Gagal Update Gaji Pokok');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $gapok = MasterGajiPokok::findOrFail($id);
            $golonganName = $gapok->golongan;
            $gapok->delete();

            DB::commit();
            return $this->sendSuccess('Master Gaji Pokok Golongan ' . $golonganName . ' berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_GAPOK_DESTROY]', 'Gagal menghapus data master gaji pokok.', 'Gagal Hapus Gaji Pokok');
        }
    }
}
