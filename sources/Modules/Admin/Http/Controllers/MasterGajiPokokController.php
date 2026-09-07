<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MiddlewareController;
use Illuminate\Support\Facades\DB;
use App\Models\MasterGajiPokok;
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
        return view('admin::master-data.gaji-pokok.index', [
            'title' => 'Master Matriks Gaji Pokok Pegawai',
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
                return '<span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 0.95rem;">' . $row->golongan . '</span>';
            })
            ->addColumn('gapok_100_formatted', function ($row) {
                return '<strong class="text-success" style="font-size: 0.95rem;">Rp ' . number_format($row->gaji_pokok_100, 0, ',', '.') . '</strong>';
            })
            ->addColumn('gapok_80_formatted', function ($row) {
                return '<span class="text-dark font-weight-bold">Rp ' . number_format($row->gaji_pokok_80, 0, ',', '.') . '</span>';
            })
            ->addColumn('berkala_formatted', function ($row) {
                $html = '<div style="font-size: 8pt;" class="text-muted">';
                $html .= '<div><strong>2 Thn:</strong> Rp ' . number_format($row->tahun_2, 0, ',', '.') . ' | <strong>4 Thn:</strong> Rp ' . number_format($row->tahun_4, 0, ',', '.') . '</div>';
                $html .= '<div><strong>6 Thn:</strong> Rp ' . number_format($row->tahun_6, 0, ',', '.') . ' | <strong>8 Thn:</strong> Rp ' . number_format($row->tahun_8, 0, ',', '.') . '</div>';
                $html .= '<div><strong>10 Thn:</strong> Rp ' . number_format($row->tahun_10, 0, ',', '.') . '</div>';
                $html .= '</div>';
                return $html;
            })
            ->addColumn('keterangan_display', function ($row) {
                return $row->keterangan ? '<span class="text-secondary small">' . nl2br(e($row->keterangan)) . '</span>' : '<span class="text-muted font-italic">-</span>';
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<button type="button" class="btn btn-xs btn-primary btn-modal mr-1" data-url="' . route('admin.master-gaji-pokok.edit', $row->id) . '" title="Edit"><i class="fas fa-edit"></i> Edit</button>';
                $btnDelete = '<button type="button" class="btn btn-xs btn-danger btn-delete" data-url="' . route('admin.master-gaji-pokok.destroy', $row->id) . '" data-name="Golongan ' . $row->golongan . '" title="Hapus"><i class="fas fa-trash"></i></button>';
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
