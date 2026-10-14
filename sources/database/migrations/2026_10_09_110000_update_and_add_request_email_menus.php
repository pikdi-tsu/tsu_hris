<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update menu ID 110 (Sisi Pengelolaan SDM & PIKDI)
        DB::table('system_menu_sidebars')->where('id', 110)->update([
            'name'       => 'Pengelolaan Request Email',
            'icon'       => 'fas fa-tasks',
            'order'      => 4,
            'updated_at' => now(),
        ]);

        // 2. Insert menu ID 111 (Sisi Pegawai / Mandiri)
        DB::table('system_menu_sidebars')->updateOrInsert(
            ['route' => 'users.request-email.index'],
            [
                'name'            => 'Pengajuan Akun Email',
                'icon'            => 'fas fa-paper-plane',
                'type'            => 'item',
                'route'           => 'users.request-email.index',
                'permission_name' => null, // Dapat diakses oleh semua pegawai login
                'parent_id'       => 77,   // Rumpun ADMINISTRATION
                'order'           => 3,
                'isactive'        => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_menu_sidebars')->where('route', 'users.request-email.index')->delete();

        DB::table('system_menu_sidebars')->where('id', 110)->update([
            'name'       => 'Request Akun Email',
            'icon'       => 'fas fa-envelope-open-text',
            'order'      => 3,
            'updated_at' => now(),
        ]);
    }
};
