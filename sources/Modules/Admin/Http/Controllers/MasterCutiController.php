<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use Illuminate\Support\Facades\Auth;

use App\Models\DataDosenTendik;
use App\Models\MasterCuti;

class MasterCutiController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:master-cuti');
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    public function index()
    {
        return view('admin::master-data.cuti.index', ['title' => 'Data Master Cuti']);
    }

    public function datatable()
    {
        $data = MasterCuti::query()->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('is_active', function ($row) {
                if ($row->is_active === '1') return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>';
                return '<span class="badge badge-secondary"><i class="fas fa-times-circle"></i> Non-Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                return $this->getActionButtons($row, 'admin:master-cuti', [
                    'use_modal'  => true,
                    'edit_url' => route('admin.master-cuti.edit', $row->id),
                    'can_delete' => true,
                    'delete_url' => route('admin.master-cuti.destroy', $row->id),
                ]);
            })
            ->rawColumns(['is_active', 'action'])
            ->make(true);
    }

    public function create()
    {
        $this->guard('create', 'admin:master-cuti');
        return view('admin::master-data.cuti.create_modal');
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-cuti');

        $request->validate([
            'jeniscuti'     => 'required|string|max:255',
            'durasicuti'    => 'required|integer',
            'minimalhari'   => 'required|integer',
        ]);

        try {
            MasterCuti::create([
                'jeniscuti'   => $request->jeniscuti,
                'durasicuti'  => $request->durasicuti,
                'minimalhari' => $request->minimalhari,
                'is_active'   => '1',
                'created_at'  => date("Y-m-d H:i:s"),
                'created_by'  => Auth::check() ? $this->getCurrentProfile()->nik : 'System',
                // 'updated_by'  => auth()->check() ? auth()->user()->name : 'System',
            ]);

            return back()->with('success', 'Master Cuti Berhasil Ditambahkan.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_CUTI_STORE_FAIL]',
                'Gagal menyimpan master cuti.',
                'Create Master Cuti.',
                $request
            );
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:master-cuti');
        $cuti = MasterCuti::findOrFail($id);
        return view('admin::master-data.cuti.edit_modal', compact('cuti'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-cuti');
        $cuti = MasterCuti::findOrFail($id);

        $request->validate([
            'jeniscuti'   => 'required|string|max:255',
            'durasicuti'  => 'required|integer',
            'minimalhari' => 'required|integer',
            'is_active'   => 'required|in:0,1'
        ]);

        try {
            $cuti->update([
                'jeniscuti'   => $request->jeniscuti,
                'durasicuti'  => $request->durasicuti,
                'minimalhari' => $request->minimalhari,
                'is_active'   => $request->is_active,
                'updated_at'  => date("Y-m-d H:i:s"),
                'updated_by'  => Auth::check() ? $this->getCurrentProfile()->nik : 'System',
            ]);

            return back()->with('success', 'Master Cuti Berhasil Diperbarui!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_CUTI_UPD_FAIL]',
                'Gagal menyimpan perubahan master cuti.',
                "Update Master Cuti ID: $id.",
                $request
            );
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:master-cuti');
        $cuti = MasterCuti::findOrFail($id);

        try {
            $cuti->delete();
            return back()->with('success', 'Master Cuti Berhasil Dihapus.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_CUTI_DEL_FAIL]',
                'Gagal menghapus master cuti karena masih digunakan atau kesalahan sistem.',
                "Delete Master Cuti ID: $id."
            );
        }
    }
}
