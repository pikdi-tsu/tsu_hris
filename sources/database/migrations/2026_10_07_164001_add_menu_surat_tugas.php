<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\System\Models\MenuSidebar;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $exists = DB::table('system_menu_sidebars')->where('route', 'admin.surat-tugas.index')->exists();
        if (!$exists) {
            DB::table('system_menu_sidebars')->insert([
                'name'            => 'Surat Tugas (Dinas Luar)',
                'icon'            => 'fas fa-briefcase',
                'type'            => 'item',
                'route'           => 'admin.surat-tugas.index',
                'permission_name' => null, // accessible to logged-in admin staff
                'parent_id'       => 29,
                'order'           => 5,
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
        DB::table('system_menu_sidebars')->where('route', 'admin.surat-tugas.index')->delete();
    }
};
