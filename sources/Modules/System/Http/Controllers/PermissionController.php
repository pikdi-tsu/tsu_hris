<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use App\Services\TsuErrorHandlerService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class PermissionController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('system:permission');
    }

    public function index()
    {
        $this->guard('view', 'system:permission');

        $allPerms = Permission::all();
        $stats = [
            'total' => $allPerms->count(),
            'admin' => $allPerms->filter(fn($p) => str_starts_with($p->name, 'admin:'))->count(),
            'users' => $allPerms->filter(fn($p) => str_starts_with($p->name, 'users:'))->count(),
            'system' => $allPerms->filter(fn($p) => str_starts_with($p->name, 'system:'))->count(),
        ];

        $title = 'Role Permissions';
        $menu = 'role_permissions';
        $menuIcon = \Modules\System\Models\MenuSidebar::where('route', 'system.permission.index')->value('icon') ?? 'fas fa-file-shield';

        return view('system::permission.index', compact('stats', 'title', 'menu', 'menuIcon'));
    }

    public function datatable()
    {
        $this->guard('view', 'system:permission');

        // Ambil permission lokal
        $data = Permission::query()->orderBy('name', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('name', function($row) {
                $parts = explode(':', $row->name);
                $modul = $parts[0] ?? '';
                $badgeStyle = 'background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;';
                if ($modul === 'admin') {
                    $badgeStyle = 'background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;';
                } elseif ($modul === 'users') {
                    $badgeStyle = 'background:#dcfce7; color:#166534; border:1px solid #bbf7d0;';
                } elseif ($modul === 'system') {
                    $badgeStyle = 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;';
                }

                return '<div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                            <span class="badge font-weight-700" style="' . $badgeStyle . ' font-size: 0.73rem; padding: 0.25rem 0.5rem; border-radius: 4px;">' . e(strtoupper($modul)) . '</span>
                            <code class="font-weight-600 px-2 py-1" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; font-size:0.83rem; color:var(--tsu-primary-dark, #094b54);">' . e($row->name) . '</code>
                        </div>';
            })
            ->addColumn('guard_name', function($row){
                return '<span class="badge badge-light border text-secondary font-weight-600" style="padding: 0.3rem 0.6rem; border-radius: 6px;"><i class="fas fa-shield-alt mr-1 text-primary"></i>'.$row->guard_name.'</span>';
            })
            ->addColumn('action', function ($row) {
                return $this->getActionButtons($row, 'system:permission', [
                    'delete_url' => route('system.permission.destroy', $row->id),
                ]);
            })
            ->rawColumns(['name', 'guard_name', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'system:permission');

        $request->validate([
            'name' => ['required', Rule::unique(config('app.table.permissions'), 'name')->where('guard_name', 'web')]
        ]);

        try {
            Permission::create(['name' => $request->name, 'guard_name' => 'web']);
            return back()->with('success', 'Permission baru berhasil dibuat!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_PERM_STORE_FAIL]', 'Gagal menyimpan permission baru.', 'Gagal Create Permission.', $request);
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'system:permission');

        $role = Role::query()->findOrFail($id);

        $permissions = Permission::query()->orderBy('name')->get();

        $groupedPermissions = $permissions->groupBy(function($item){
            $parts = explode(':', $item->name);
            return ucfirst($parts[0]);
        });

        // Ambil permission yang SUDAH dimiliki role ini (untuk auto-check)
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('system::role.edit_modal', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'system:permission');

        $request->validate([
            'name' => ['required', Rule::unique(config('app.table.permissions'), 'name')->ignore($id)->where('guard_name', 'web')]
        ]);

        try {
            $permission = Permission::query()->findOrFail($id);
            $permission->update(['name' => $request->name]);
            return back()->with('success', 'Nama Permission berhasil diperbarui!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_PERM_UPDATE_FAIL]', 'Gagal memperbarui permission.', "Gagal Update Permission ID: $id.", $request);
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'system:permission');

        try {
            $permission = Permission::query()->findOrFail($id);
            $permission->delete();
            return back()->with('success', 'Permission berhasil dihapus!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_PERM_DELETE_FAIL]', 'Gagal menghapus permission.', "Gagal Hapus Permission ID: $id.");
        }
    }
}
