<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MasterUnit;
use App\Models\MasterJabatanStruktural;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use App\Traits\ApiResponseTrait;

class MasterUnitController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:master-unit');
    }

    public function index()
    {
        return view('admin::master-data.unit.index', ['title' => 'Master Data Unit']);
    }

    public function datatable()
    {
        $data = MasterUnit::with(['kepalaJabatan', 'parent'])->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('unit_induk', function ($row) {
                return $row->parent ? $row->parent->nama_unit : '-';
            })
            ->addColumn('kepala_unit', function ($row) {
                return $row->kepalaJabatan ? $row->kepalaJabatan->nama_jabatan : '-';
            })
            ->addColumn('action', function ($row) {
                return $this->getActionButtons($row, 'admin:master-unit', [
                    'use_modal'  => true,
                    'edit_url' => route('admin.master-unit.edit', $row->id),
                    'delete_url' => route('admin.master-unit.destroy', $row->id),
                ]);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $this->guard('create', 'admin:master-unit');
        $jabatans = MasterJabatanStruktural::orderBy('nama_jabatan', 'asc')->get();
        $parentUnits = MasterUnit::orderBy('nama_unit', 'asc')->get();
        return view('admin::master-data.unit._modal', compact('jabatans', 'parentUnits'));
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-unit');

        $request->validate([
            'nama_unit' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'kepala_jabatan_id' => 'nullable|exists:master_jabatan_strukturals,id',
            'parent_unit_id' => 'nullable|exists:master_units,id',
            'kuota_mpp' => 'nullable|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            $kepalaJabatanId = $request->kepala_jabatan_id;

            if ($request->has('auto_create_jabatan')) {
                $jabatan = MasterJabatanStruktural::create([
                    'nama_jabatan' => 'Kepala ' . $request->nama_unit,
                    'is_unit_specific' => 'Y'
                ]);
                $kepalaJabatanId = $jabatan->id;
            }

            MasterUnit::create([
                'nama_unit' => $request->nama_unit,
                'keterangan' => $request->keterangan,
                'kepala_jabatan_id' => $kepalaJabatanId,
                'parent_unit_id' => $request->parent_unit_id,
                'kuota_mpp' => $request->kuota_mpp ?? 0
            ]);

            DB::commit();
            return $this->sendSuccess('Master Unit berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_MASTER_UNIT_STORE]',
                'Gagal menyimpan data master unit.',
                'Gagal Create Master Unit.'
            );
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:master-unit');
        $unit = MasterUnit::findOrFail($id);
        $jabatans = MasterJabatanStruktural::orderBy('nama_jabatan', 'asc')->get();
        $parentUnits = MasterUnit::where('id', '!=', $id)->orderBy('nama_unit', 'asc')->get();
        return view('admin::master-data.unit._modal', compact('unit', 'jabatans', 'parentUnits'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-unit');
        $unit = MasterUnit::findOrFail($id);

        $request->validate([
            'nama_unit' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'kepala_jabatan_id' => 'nullable|exists:master_jabatan_strukturals,id',
            'parent_unit_id' => 'nullable|exists:master_units,id',
            'kuota_mpp' => 'nullable|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            $unit->update([
                'nama_unit' => $request->nama_unit,
                'keterangan' => $request->keterangan,
                'kepala_jabatan_id' => $request->kepala_jabatan_id,
                'parent_unit_id' => $request->parent_unit_id,
                'kuota_mpp' => $request->kuota_mpp ?? 0
            ]);

            DB::commit();
            return $this->sendSuccess('Data Master Unit berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_MASTER_UNIT_UPDATE]',
                'Gagal memperbarui data master unit.',
                "Gagal Update Master Unit ID: $id."
            );
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:master-unit');
        $unit = MasterUnit::findOrFail($id);

        DB::beginTransaction();
        try {
            $unit->delete();
            DB::commit();
            return $this->sendSuccess('Data Master Unit berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson(
                $e,
                '[TSU_MASTER_UNIT_DELETE]',
                'Gagal menghapus data karena kesalahan sistem atau data sedang digunakan.',
                "Gagal Delete Unit ID: $id."
            );
        }
    }
}
