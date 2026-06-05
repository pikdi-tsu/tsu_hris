<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TsuErrorHandlerService;
use Illuminate\Support\Facades\Auth;

use App\Models\MasterIzin;
use App\Models\DataDosenTendik;


class MasterIzinController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:master-izin');
    }

    private function getCurrentProfile()
    {
        return DataDosenTendik::where('user_id', Auth::id())->first();
    }

    public function index()
    {
        return view('admin::master-data.izin.index', ['title' => 'Data Master Izin']);
    }

    public function datatable()
    {
        $data = MasterIzin::query()->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('is_active', function ($row) {
                if ($row->is_active === '1') return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>';
                return '<span class="badge badge-secondary"><i class="fas fa-times-circle"></i> Non-Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                return $this->getActionButtons($row, 'admin:master-izin', [
                    'use_modal'  => true,
                    'edit_url' => route('admin.master-izin.edit', $row->id),
                    'can_delete' => true,
                    'delete_url' => route('admin.master-izin.destroy', $row->id),
                ]);
            })
            ->rawColumns(['is_active', 'action'])
            ->make(true);
    }

    public function create()
    {
        $this->guard('create', 'admin:master-izin');
        return view('admin::master-data.izin.create_modal');
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:master-izin');

        $request->validate([
            'jenisizin' => 'required|string|max:255',
        ]);

        try {
            MasterIzin::create([
                'jenisizin' => $request->jenisizin,
                'is_active'   => '1',
                'created_at'  => date("Y-m-d H:i:s"),
                'created_by'  => Auth::check() ? $this->getCurrentProfile()->nik : 'System',
            ]);

            return back()->with('success', 'Master Izin Berhasil Ditambahkan.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_IZIN_STORE_FAIL]',
                'Gagal menyimpan master izin.',
                'Create Master Izin.',
                $request
            );
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'admin:master-izin');
        $izin = MasterIzin::findOrFail($id);
        return view('admin::master-data.izin.edit_modal', compact('izin'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:master-izin');
        $izin = MasterIzin::findOrFail($id);

        $request->validate([
            'jenisizin' => 'required|string|max:255',
            'is_active'   => 'required|in:0,1'
        ]);

        try {
            $izin->update([
                'jenisizin'   => $request->jenisizin,
                'is_active'   => $request->is_active,
                'updated_at'  => date("Y-m-d H:i:s"),
                'updated_by'  => Auth::check() ? $this->getCurrentProfile()->nik : 'System',
            ]);

            return back()->with('success', 'Master Izin berhasil diperbarui!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_IZIN_UPD_FAIL]',
                'Gagal menyimpan perubahan master izin.',
                "Update Master Izin ID: $id.",
                $request
            );
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'admin:master-izin');
        $izin = MasterIzin::findOrFail($id);

        try {
            $izin->delete();
            return back()->with('success', 'Master Izin berhasil dihapus.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml(
                $e,
                '[TSU_MASTER_IZIN_DEL_FAIL]',
                'Gagal menghapus master izin karena masih digunakan atau kesalahan sistem.',
                "Delete Master Izin ID: $id."
            );
        }
    }
}
