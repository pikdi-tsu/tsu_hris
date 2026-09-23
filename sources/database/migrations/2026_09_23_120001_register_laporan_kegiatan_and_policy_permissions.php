<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Modules\System\Models\MenuSidebar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cache permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Laporan Kegiatan SDM
            'admin:laporan-kegiatan-sdm:view',
            'admin:laporan-kegiatan-sdm:create',
            'admin:laporan-kegiatan-sdm:edit',
            'admin:laporan-kegiatan-sdm:delete',

            // Absensi Kegiatan
            'admin:absensi-kegiatan:view',
            'admin:absensi-kegiatan:create',
            'admin:absensi-kegiatan:edit',
            'admin:absensi-kegiatan:delete',

            // HR Policy
            'admin:hr-policy:view',
            'admin:hr-policy:create',
            'admin:hr-policy:edit',
            'admin:hr-policy:delete',

            // Disposisi Unit
            'admin:disposisi-unit:view',
            'admin:disposisi-unit:create',
            'admin:disposisi-unit:edit',
            'admin:disposisi-unit:delete',
        ];

        // 1. Buat permissions
        $createdPerms = [];
        foreach ($permissions as $permName) {
            $createdPerms[] = Permission::firstOrCreate([
                'name'       => $permName,
                'guard_name' => 'web',
            ]);
        }

        // 2. Berikan ke role admin utama
        $adminRoles = Role::whereIn('name', ['super admin hris', 'admin hris testing'])->get();
        foreach ($adminRoles as $role) {
            $role->givePermissionTo($permissions);
        }

        // 3. Update MenuSidebar dengan permission_name masing-masing
        MenuSidebar::where('route', 'admin.laporan-kegiatan-sdm.index')->update([
            'permission_name' => 'admin:laporan-kegiatan-sdm:view'
        ]);

        MenuSidebar::where('route', 'admin.absensi-kegiatan.index')->update([
            'permission_name' => 'admin:absensi-kegiatan:view'
        ]);

        MenuSidebar::where('route', 'admin.hr-policy.renstra.index')->update([
            'permission_name' => 'admin:hr-policy:view'
        ]);
        MenuSidebar::where('route', 'admin.hr-policy.peraturan.index')->update([
            'permission_name' => 'admin:hr-policy:view'
        ]);
        MenuSidebar::where('route', 'admin.hr-policy.sop.index')->update([
            'permission_name' => 'admin:hr-policy:view'
        ]);
        MenuSidebar::where('route', 'admin.hr-policy.tupoksi.index')->update([
            'permission_name' => 'admin:hr-policy:view'
        ]);

        MenuSidebar::where('route', 'admin.disposisi-unit.index')->update([
            'permission_name' => 'admin:disposisi-unit:view'
        ]);

        // Reset cache permission kembali
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $routes = [
            'admin.laporan-kegiatan-sdm.index',
            'admin.absensi-kegiatan.index',
            'admin.hr-policy.renstra.index',
            'admin.hr-policy.peraturan.index',
            'admin.hr-policy.sop.index',
            'admin.hr-policy.tupoksi.index',
            'admin.disposisi-unit.index',
        ];

        MenuSidebar::whereIn('route', $routes)->update(['permission_name' => null]);

        $permissions = [
            'admin:laporan-kegiatan-sdm:view',
            'admin:laporan-kegiatan-sdm:create',
            'admin:laporan-kegiatan-sdm:edit',
            'admin:laporan-kegiatan-sdm:delete',
            'admin:absensi-kegiatan:view',
            'admin:absensi-kegiatan:create',
            'admin:absensi-kegiatan:edit',
            'admin:absensi-kegiatan:delete',
            'admin:hr-policy:view',
            'admin:hr-policy:create',
            'admin:hr-policy:edit',
            'admin:hr-policy:delete',
            'admin:disposisi-unit:view',
            'admin:disposisi-unit:create',
            'admin:disposisi-unit:edit',
            'admin:disposisi-unit:delete',
        ];

        Permission::whereIn('name', $permissions)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
