<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $exists = DB::table('system_menu_sidebars')->where('route', 'users.surat-tugas.index')->exists();
        if (!$exists) {
            DB::table('system_menu_sidebars')->insert([
                'name'            => 'Surat Tugas Saya',
                'icon'            => 'fas fa-briefcase',
                'type'            => 'item',
                'route'           => 'users.surat-tugas.index',
                'permission_name' => null,
                'parent_id'       => 80,
                'order'           => 6,
                'isactive'        => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('system_menu_sidebars')->where('route', 'users.surat-tugas.index')->delete();
    }
};
