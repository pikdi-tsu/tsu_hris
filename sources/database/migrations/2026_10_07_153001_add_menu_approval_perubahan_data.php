<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\System\Models\MenuSidebar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $approvalGroup = MenuSidebar::where('name', 'APPROVAL')->whereNull('parent_id')->first();
        $parentId = $approvalGroup ? $approvalGroup->id : 83;

        MenuSidebar::updateOrCreate(
            ['route' => 'admin.approval-perubahan-data.index'],
            [
                'name' => 'Approval Perubahan Data',
                'icon' => 'fas fa-user-check',
                'type' => 'item',
                'permission_name' => 'admin:data-karyawan:view',
                'parent_id' => $parentId,
                'order' => 4,
                'isactive' => 1,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MenuSidebar::where('route', 'admin.approval-perubahan-data.index')->delete();
    }
};
