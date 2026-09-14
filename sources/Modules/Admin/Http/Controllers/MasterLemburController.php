<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\MasterLembur;
use App\Models\LemburKaryawan;
use Modules\System\Models\MenuSidebar;
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
        $stats = [
            'total'            => MasterLembur::count(),
            'active'           => MasterLembur::where('is_active', '1')->count(),
            'total_pengajuan'  => LemburKaryawan::count(),
            'lembur_bulan_ini' => LemburKaryawan::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        $menuIcon = MenuSidebar::where('route', 'admin.master-lembur.index')->value('icon') ?? 'fas fa-business-time';

        return view('admin::master-data.lembur.index', [
            'title'    => 'Data Master Lembur',
            'menu'     => 'master-lembur',
            'menuIcon' => $menuIcon,
            'stats'    => $stats,
        ]);
    }

    public function datatable()
    {
        $data = MasterLembur::query()->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('jenislembur', function($row) {
                return '<span class="font-weight-bold text-dark" style="font-size: 0.88rem;">' . e($row->jenislembur) . '</span>';
            })
            ->editColumn('keterangan', function($row) {
                if (!$row->keterangan) {
                    return '<span class="text-muted text-xs font-italic">Tidak ada keterangan</span>';
                }
                return '<span class="text-dark" style="font-size: 0.85rem;">' . e($row->keterangan) . '</span>';
            })
            ->addColumn('is_active', function($row) {
                if ($row->is_active === '1') {
                    return '<div class="text-center"><span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.76rem;">Aktif</span></div>';
                }
                return '<div class="text-center"><span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.76rem;">Non-Aktif</span></div>';
            })
            ->addColumn('action', function ($row) {
                $canEdit   = auth()->user()->can('admin:master-lembur:edit');
                $canDelete = auth()->user()->can('admin:master-lembur:delete');

                $btn = '<div class="d-flex align-items-center justify-content-center" style="gap: 0.35rem;">';

                if ($canEdit) {
                    $btn .= '<button type="button" data-url="'.route('admin.master-lembur.edit', $row->id).'" class="btn btn-sm btn-edit" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Edit Master Lembur">
                                <i class="fas fa-pen"></i>
                            </button>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                if ($canDelete) {
                    $btn .= '<form action="'.route('admin.master-lembur.destroy', $row->id).'" method="POST" style="display:inline;" class="form-delete">
                                '.csrf_field().' '.method_field('DELETE').'
                                <button type="submit" class="btn btn-sm btn-delete" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Hapus Master Lembur">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['jenislembur', 'keterangan', 'is_active', 'action'])
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

        if (LemburKaryawan::where('id_mlembur', $id)->exists()) {
            return back()->with('error', 'Gagal Menghapus! Master lembur ini masih terikat dengan riwayat pengajuan lembur karyawan.');
        }

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
