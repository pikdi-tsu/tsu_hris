<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use App\Services\TsuErrorHandlerService;
use Illuminate\Support\Str;
use Modules\System\Models\MenuSidebar;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

// Import Spatie

class MenuController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('system:menu');
    }

    public function index()
    {
        $permissions = Permission::query()->orderBy('name')->pluck('name', 'name');
        $parents = $this->getHierarchicalParents();

        $stats = [
            'total'  => MenuSidebar::count(),
            'root'   => MenuSidebar::whereNull('parent_id')->count(),
            'sub'    => MenuSidebar::whereNotNull('parent_id')->count(),
            'active' => MenuSidebar::where('isactive', 1)->count(),
        ];

        $menuIcon = MenuSidebar::where('route', 'system.menu.index')->value('icon') ?? 'fas fa-bars';

        return view('system::menu.index', [
            'title'       => 'Manajemen Menu Sidebar',
            'menu'        => 'menu',
            'menuIcon'    => $menuIcon,
            'stats'       => $stats,
            'permissions' => $permissions,
            'parents'     => $parents
        ]);
    }

    private function getHierarchicalParents($ignoreId = null)
    {
        $nodes = MenuSidebar::query()->whereNull('parent_id')->orderBy('order')->with('children')->get();
        $options = [];

        foreach ($nodes as $node) {
            if ($node->id === $ignoreId) {
                continue;
            }

            $options[$node->id] = $node->name;

            // Panggil fungsi rekursif child
            $this->recurseChildren($node, $options, $ignoreId, 1);
        }

        return $options;
    }

    private function recurseChildren($parent, &$options, $ignoreId, $depth)
    {
        foreach ($parent->children as $child) {
            if ($child->id === $ignoreId) {
                continue;
            }

            // Prefix menjorok (Contoh: "— — Nama Submenu")
            $prefix = str_repeat('— ', $depth);
            $options[$child->id] = $prefix . $child->name;

            // Cek children
            $this->recurseChildren($child, $options, $ignoreId, $depth + 1);
        }
    }

    public function datatable()
    {
        // Ambil data
        $allMenus = MenuSidebar::query()->orderBy('order', 'asc')->get();

        // Urutan hirarki (Bapak -> Anak -> Cucu)
        $sortedData = $this->sortTree($allMenus);

        return DataTables::of($sortedData)
            ->addIndexColumn()
            ->editColumn('name', function ($d) {
                // VISUALISASI HIERARKI (POHON)
                $padding = $d->depth * 22;
                $branch = '';
                if ($d->depth > 0) {
                    $branch = '<i class="fas fa-level-up-alt fa-rotate-90 mr-2" style="font-size: 0.75rem; color: #94a3b8; margin-left: -12px;"></i>';
                }
                
                $styleTitle = match ((int)$d->depth) {
                    0 => 'font-weight: 700; color: #094b54; font-size: 0.88rem;',
                    1 => 'font-weight: 600; color: #1d7a87; font-size: 0.84rem;',
                    default => 'font-weight: 500; color: #475569; font-size: 0.82rem;',
                };

                $typeBadge = '';
                if ($d->depth === 0 && (!$d->route || $d->route === '#')) {
                    $typeBadge = ' <span class="badge ml-1" style="background: rgba(9, 75, 84, 0.08); color: #094b54; font-size: 0.68rem; font-weight: 600; padding: 2px 6px; border-radius: 4px;">Folder</span>';
                }

                return '<div style="padding-left: '.$padding.'px; '.$styleTitle.'" class="d-inline-flex align-items-center">' . $branch . '<span>' . e($d->name) . '</span>' . $typeBadge . '</div>';
            })
            ->editColumn('icon', function ($d) {
                if (!$d->icon) {
                    return '<div class="text-center text-muted" style="font-size: 0.85rem;">-</div>';
                }
                return '<div class="text-center">
                            <div class="d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(9, 75, 84, 0.08); color: #094b54; font-size: 0.9rem;" title="'.e($d->icon).'">
                                <i class="'.e($d->icon).'"></i>
                            </div>
                        </div>';
            })
            ->editColumn('route', function($d) {
                return $d->route && $d->route !== '#'
                    ? '<code style="background: #f8fafc; color: #0f172a; padding: 3px 8px; border-radius: 6px; font-size: 0.8rem; border: 1px solid #e2e8f0; font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-weight: 600;">'.e($d->route).'</code>'
                    : '<span class="badge" style="background: #f1f5f9; color: #94a3b8; border: 1px dashed #cbd5e1; font-weight: 500; font-size: 0.75rem; padding: 4px 8px; border-radius: 6px;"><i class="fas fa-folder mr-1"></i> Label / Group</span>';
            })
            ->addColumn('permission', function ($d) {
                return $d->permission_name
                    ? '<span class="badge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;"><i class="fas fa-key mr-1" style="font-size: 0.7rem;"></i> '.e($d->permission_name).'</span>'
                    : '<span class="badge" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;"><i class="fas fa-globe mr-1" style="font-size: 0.7rem;"></i> Public</span>';
            })
            ->addColumn('status', function ($d) {
                if ($d->isactive) {
                    return '<div class="text-center"><span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #047857; border: 1px solid rgba(16, 185, 129, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;"><i class="fas fa-check-circle mr-1"></i> Aktif</span></div>';
                }
                return '<div class="text-center"><span class="badge" style="background: rgba(100, 116, 139, 0.12); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25); font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.75rem;"><i class="fas fa-times-circle mr-1"></i> Non-Aktif</span></div>';
            })
            ->addColumn('action', function ($row) {
                $canEdit   = auth()->user()->can('system:menu:edit');
                $canDelete = auth()->user()->can('system:menu:delete');
                $isSystemCore = Str::contains($row->route, 'dashboard');

                if (!$canEdit && !$canDelete) {
                    return '<div class="text-center">
                                <span class="badge" style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.35rem 0.65rem; font-size: 0.75rem;" title="Akses Dibatasi">
                                    <i class="fas fa-lock mr-1"></i> Locked
                                </span>
                            </div>';
                }

                $btn = '<div class="d-flex align-items-center justify-content-center" style="gap: 0.35rem;">';

                if ($canEdit) {
                    $btn .= '<a href="'.route('system.menu.edit', $row->id).'" class="btn btn-sm btn-edit" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Edit Menu">
                                <i class="fas fa-pen"></i>
                            </a>';
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                if ($canDelete) {
                    if ($isSystemCore) {
                        $btn .= '<button class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed;" title="Menu Inti Sistem (Terkunci)"><i class="fas fa-lock"></i></button>';
                    } else {
                        $btn .= '<form action="'.route('system.menu.destroy', $row->id).'" method="POST" style="display:inline;">
                                        '.csrf_field().' '.method_field('DELETE').'
                                        <button type="submit" class="btn btn-sm btn-delete" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; transition: all 0.2s;" title="Hapus Menu"><i class="fas fa-trash"></i></button>
                                    </form>';
                    }
                } else {
                    $btn .= '<button type="button" class="btn btn-sm" disabled style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.8rem; cursor: not-allowed; opacity: 0.6;" title="No Access">
                                <i class="fas fa-lock"></i>
                             </button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->setRowClass(function ($d) {
                return $d->depth === 0 ? 'tsu-row-root' : '';
            })
            ->rawColumns(['name', 'icon', 'route', 'permission', 'status', 'action'])
            ->make(true);
    }

    private function sortTree($menus, $parentId = null, $depth = 0)
    {
        $result = collect([]);
        $children = $menus->where('parent_id', $parentId)->sortBy('order');

        foreach ($children as $child) {
            // Depth object menu
            $child->setAttribute('depth', $depth);

            // Masukkan parent ke hasil
            $result->push($child);

            // Cari recursive children dengan depth + 1
            $result = $result->merge($this->sortTree($menus, $child->id, $depth + 1));
        }

        return $result;
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'system:menu');

        $request->validate([
            'name' => 'required',
            'order' => 'required|integer',
        ]);

        try {
            MenuSidebar::query()->create([
                'name'            => $request->name,
                'icon'            => $request->icon ?: 'fas fa-box',
                'route'           => $request->route,
                'permission_name' => $request->permission_name,
                'parent_id'       => $request->parent_id,
                'order'           => $request->order,
                'isactive'        => $request->has('isactive') ? 1 : 0,
            ]);

            return back()->with('success', 'Menu berhasil dibuat!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_MENU_STORE_FAIL]', 'Gagal menyimpan data menu baru.', 'Gagal Create Menu.', $request);
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'system:menu');

        $menu = MenuSidebar::query()->findOrFail($id);
        $permissions = Permission::query()->orderBy('name')->pluck('name', 'name');

        // Ambil parent
        $parents = $this->getHierarchicalParents($id);

        // KITA RETURN VIEW PARTIAL (Khusus untuk dimuat di dalam Modal)
        return view('system::menu.edit_modal', compact('menu', 'permissions', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'system:menu');

        $menu = MenuSidebar::query()->findOrFail($id);

        try {
            $menu->update([
                'name'            => $request->name,
                'icon'            => $request->icon,
                'route'           => $request->route,
                'permission_name' => $request->permission_name,
                'parent_id'       => $request->parent_id,
                'order'           => $request->order,
                'isactive'        => $request->has('isactive') ? 1 : 0,
            ]);

            return back()->with('success', 'Menu berhasil diupdate!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_MENU_UPDATE_FAIL]', 'Gagal memperbarui data menu.', "Gagal Update Menu ID: $id.", $request);
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'system:menu');

        try {
            $menu = MenuSidebar::withCount('children')->findOrFail($id);

            if ($menu->children_count > 0) {
                return redirect()->back()->with('error',
                    '<b>Gagal Menghapus!</b>
                <br>Menu ini masih memiliki
                <b>'.$menu->children_count.' Sub-Menu</b>di dalamnya.
                <br>Silakan hapus atau pindahkan sub-menu terlebih dahulu.'
                );
            }

            if (!empty($menu->permission_name)) {
                // Cek Permission
                $permissionExists = Permission::query()->where('name', $menu->permission_name)->exists();

                if ($permissionExists) {
                    $linkPermission = route('system.permission.index', ['search' => $menu->permission_name]);

                    return redirect()->back()->with('error',
                        '<b>Gagal Menghapus!</b><br>'.
                        'Menu ini masih terikat dengan Permission: <b>'.$menu->permission_name.'</b>.<br><br>'.
                        'Demi keamanan data, Anda wajib menghapus data Permission-nya terlebih dahulu sebelum menghapus Menu ini.<br><br>'.
                        '<a href="'.$linkPermission.'" class="btn btn-danger btn-xs text-white shadow-sm">'.
                        '<i class="fas fa-arrow-right"></i> Hapus Permission Disini'.
                        '</a>'
                    );
                }

                // Cek Penggunaan di Role (Active Usage)
                $permission = Permission::query()->where('name', $menu->permission_name)->first();

                if ($permission) {
                    $usedByRoles = $permission->roles()->count();

                    if ($usedByRoles > 0) {
                        return redirect()->back()->with('error',
                            '<b>Gagal Menghapus!</b><br>'.
                            'Permission menu ini (<b>'.$menu->permission_name.'</b>) sedang aktif digunakan oleh <b>'.$usedByRoles.' Role</b>.<br>'.
                            'Silakan uncheck/cabut akses permission ini dari Role terkait di Manajemen Role terlebih dahulu.'
                        );
                    }
                }
            }

            $menu->delete();

            return redirect()->route('system.menu.index')->with('success', 'Menu berhasil dihapus!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_MENU_DELETE_FAIL]', 'Gagal menghapus data menu.', "Gagal Hapus Menu ID: $id.");
        }
    }
}
