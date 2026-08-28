<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MiddlewareController;
use Illuminate\Support\Facades\DB;
use App\Models\MasterStatusKaryawan;
use App\Services\TsuErrorHandlerService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Traits\ApiResponseTrait;

class MasterStatusKaryawanController extends MiddlewareController
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->registerPermissions('admin:master-status-karyawan');
    }

    public function index()
    {
        return view('admin::master-data.status-karyawan.index', ['title' => 'Master Data Status Karyawan']);
    }

    public function datatable()
    {
        $data = MasterStatusKaryawan::orderBy('nama_status', 'asc');
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('status', function($row){
                if($row->is_active == 'Y'){
                    return '<span class="badge badge-success">Aktif</span>';
                }
                return '<span class="badge badge-secondary">Tidak Aktif</span>';
            })
            ->addColumn('action', function($row){
                return $this->getActionButtons($row, 'admin:master-status-karyawan', [
                    'use_modal'  => true,
                    'edit_url' => route('admin.master-status-karyawan.edit', $row->id),
                    'delete_url' => route('admin.master-status-karyawan.destroy', $row->id),
                    'delete_title' => $row->is_active == 'Y' ? 'Nonaktifkan' : 'Aktifkan',
                    'delete_icon' => 'fas fa-power-off',
                    'delete_class' => $row->is_active == 'Y' ? 'btn-warning' : 'btn-success',
                    'delete_name' => $row->nama_status
                ]);
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $this->guard('create', 'admin:master-status-karyawan');
        return view('admin::master-data.status-karyawan.create_modal');
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-status-karyawan');

        $request->validate([
            'nama_status' => 'required|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            MasterStatusKaryawan::create([
                'id' => Str::uuid()->toString(),
                'nama_status' => $request->nama_status,
                'keterangan' => $request->keterangan,
                'is_active' => 'Y'
            ]);
            
            DB::commit();
            return $this->sendSuccess('Status Karyawan berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_STAT_STORE]', 'Gagal menambah status karyawan.', 'Gagal Store Status');
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:master-status-karyawan');
        $status = MasterStatusKaryawan::findOrFail($id);
        return view('admin::master-status-karyawan.edit_modal', compact('status'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-status-karyawan');
        $request->validate([
            'nama_status' => 'required|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $status = MasterStatusKaryawan::findOrFail($id);
            $status->update([
                'nama_status' => $request->nama_status,
                'keterangan' => $request->keterangan
            ]);
            
            DB::commit();
            return $this->sendSuccess('Status Karyawan berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_STAT_UPDATE]', 'Gagal memperbarui status karyawan.', 'Gagal Update Status');
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:master-status-karyawan');
        
        DB::beginTransaction();
        try {
            $status = MasterStatusKaryawan::findOrFail($id);
            
            // Toggle active status
            $newStatus = $status->is_active == 'Y' ? 'N' : 'Y';
            $status->update(['is_active' => $newStatus]);
            
            $msg = $newStatus == 'Y' ? 'diaktifkan' : 'dinonaktifkan';
            
            DB::commit();
            return $this->sendSuccess('Status Karyawan berhasil ' . $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return TsuErrorHandlerService::handleJson($e, '[M_STAT_DESTROY]', 'Gagal menonaktifkan status karyawan.', 'Gagal Destroy Status');
        }
    }
}
