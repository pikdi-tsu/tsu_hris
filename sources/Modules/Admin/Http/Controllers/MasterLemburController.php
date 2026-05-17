<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\MasterLembur;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;

class MasterLemburController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:master-lembur');
    }

    public function index()
    {
        return view('admin::master-data.lembur.index', ['title' => 'Data Master Lembur']);
    }

    public function datatable()
    {
        $data = MasterLembur::query()->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('is_active', function($row) {
                if($row->is_active === '1') return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>';
                return '<span class="badge badge-secondary"><i class="fas fa-times-circle"></i> Non-Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                return $this->getActionButtons($row, 'admin:master-lembur', [
                    'use_modal'  => true,
                    'edit_url' => route('admin.master-lembur.edit', $row->id),
                    'can_delete' => true,
                    'delete_url' => route('admin.master-lembur.destroy', $row->id),
                ]);
            })
            ->rawColumns(['is_active', 'action'])
            ->make(true);
    }

    public function create()
    {
        $this->guard('create', 'admin:master-lembur');
        return view('admin::master-data.lembur.create_modal');
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-lembur');

        $request->validate([
            'jenislembur' => 'required|string|max:255',
            'keterangan'  => 'nullable|string|max:500'
        ]);

        try {
            MasterLembur::create([
                'jenislembur' => $request->jenislembur,
                'keterangan'  => $request->keterangan,
                'is_active'   => '1',
                'created_by'  => auth()->check() ? auth()->user()->name : 'System',
                'updated_by'  => auth()->check() ? auth()->user()->name : 'System',
            ]);

            return back()->with('success', 'Master Lembur berhasil ditambahkan.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e, 
                '[TSU_MASTER_LEMBUR_STORE_FAIL]', 
                'Gagal menyimpan master lembur.', 
                'Create Master Lembur.', 
                $request
            );
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:master-lembur');
        $lembur = MasterLembur::findOrFail($id);
        return view('admin::master-data.lembur.edit_modal', compact('lembur'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-lembur');
        $lembur = MasterLembur::findOrFail($id);

        $request->validate([
            'jenislembur' => 'required|string|max:255',
            'keterangan'  => 'nullable|string|max:500',
            'is_active'   => 'required|in:0,1'
        ]);

        try {
            $lembur->update([
                'jenislembur' => $request->jenislembur,
                'keterangan'  => $request->keterangan,
                'is_active'   => $request->is_active,
                'updated_by'  => auth()->check() ? auth()->user()->name : 'System',
            ]);

            return back()->with('success', 'Master Lembur berhasil diperbarui!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e, 
                '[TSU_MASTER_LEMBUR_UPD_FAIL]', 
                'Gagal menyimpan perubahan master lembur.', 
                "Update Master Lembur ID: $id.", 
                $request
            );
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:master-lembur');
        $lembur = MasterLembur::findOrFail($id);

        try {
            $lembur->delete();
            return back()->with('success', 'Master Lembur berhasil dihapus.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e, 
                '[TSU_MASTER_LEMBUR_DEL_FAIL]', 
                'Gagal menghapus master lembur karena masih digunakan atau kesalahan sistem.', 
                "Delete Master Lembur ID: $id."
            );
        }
    }
}
