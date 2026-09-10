<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Modules\System\Models\MenuSidebar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename / Migrate Permissions
        $map = [
            'users:approvalcuti:view'   => 'users:approval-cuti:view',
            'users:approvalcuti:create' => 'users:approval-cuti:create',
            'users:approvalcuti:edit'   => 'users:approval-cuti:edit',
            'users:approvalcuti:delete' => 'users:approval-cuti:delete',

            'users:approvalizin:view'   => 'users:approval-izin:view',
            'users:approvalizin:create' => 'users:approval-izin:create',
            'users:approvalizin:edit'   => 'users:approval-izin:edit',
            'users:approvalizin:delete' => 'users:approval-izin:delete',

            'users:approvallembur:view'   => 'users:approval-lembur:view',
            'users:approvallembur:create' => 'users:approval-lembur:create',
            'users:approvallembur:edit'   => 'users:approval-lembur:edit',
            'users:approvallembur:delete' => 'users:approval-lembur:delete',
        ];

        $roles = Role::whereIn('name', ['super admin hris', 'admin hris testing', 'dosen', 'tendik'])->get();

        foreach ($map as $old => $new) {
            $existing = Permission::where('name', $old)->first();
            if ($existing) {
                $existing->name = $new;
                $existing->save();
            } else {
                $perm = Permission::firstOrCreate(['name' => $new, 'guard_name' => 'web']);
                foreach ($roles as $role) {
                    $role->givePermissionTo($perm);
                }
            }
        }

        // 2. Update MenuSidebar
        MenuSidebar::where('route', 'users.indexapprovalcuti')->update([
            'route'           => 'users.approval-cuti.index',
            'permission_name' => 'users:approval-cuti:view',
        ]);

        MenuSidebar::where('route', 'users.indexapprovalizin')->update([
            'route'           => 'users.approval-izin.index',
            'permission_name' => 'users:approval-izin:view',
        ]);

        MenuSidebar::where('route', 'users.indexapprovallembur')->update([
            'route'           => 'users.approval-lembur.index',
            'permission_name' => 'users:approval-lembur:view',
        ]);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $map = [
            'users:approval-cuti:view'   => 'users:approvalcuti:view',
            'users:approval-cuti:create' => 'users:approvalcuti:create',
            'users:approval-cuti:edit'   => 'users:approvalcuti:edit',
            'users:approval-cuti:delete' => 'users:approvalcuti:delete',

            'users:approval-izin:view'   => 'users:approvalizin:view',
            'users:approval-izin:create' => 'users:approvalizin:create',
            'users:approval-izin:edit'   => 'users:approvalizin:edit',
            'users:approval-izin:delete' => 'users:approvalizin:delete',

            'users:approval-lembur:view'   => 'users:approvallembur:view',
            'users:approval-lembur:create' => 'users:approvallembur:create',
            'users:approval-lembur:edit'   => 'users:approvallembur:edit',
            'users:approval-lembur:delete' => 'users:approvallembur:delete',
        ];

        foreach ($map as $new => $old) {
            $existing = Permission::where('name', $new)->first();
            if ($existing) {
                $existing->name = $old;
                $existing->save();
            }
        }

        MenuSidebar::where('route', 'users.approval-cuti.index')->update([
            'route'           => 'users.indexapprovalcuti',
            'permission_name' => 'users:approvalcuti:view',
        ]);

        MenuSidebar::where('route', 'users.approval-izin.index')->update([
            'route'           => 'users.indexapprovalizin',
            'permission_name' => 'users:approvalizin:view',
        ]);

        MenuSidebar::where('route', 'users.approval-lembur.index')->update([
            'route'           => 'users.indexapprovallembur',
            'permission_name' => 'users:approvallembur:view',
        ]);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
