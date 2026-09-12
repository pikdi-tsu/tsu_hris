<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Parent ID 7 is 'Master Data'
$masterDataParent = DB::table('system_menu_sidebars')->where('id', 7)->first();

if ($masterDataParent) {
    // Pindahkan Kamus Indikator KPI
    DB::table('system_menu_sidebars')
        ->where('route', 'admin.kpi.master-indikator.index')
        ->update([
            'parent_id' => 7,
            'name'      => 'Kamus Indikator KPI',
            'order'     => 60,
            'updated_at'=> now(),
        ]);

    // Pindahkan Perspektif BSC
    DB::table('system_menu_sidebars')
        ->where('route', 'admin.kpi.perspektif.index')
        ->update([
            'parent_id' => 7,
            'name'      => 'Perspektif BSC',
            'order'     => 61,
            'updated_at'=> now(),
        ]);

    // Pindahkan Periode Penilaian
    DB::table('system_menu_sidebars')
        ->where('route', 'admin.kpi.periode.index')
        ->update([
            'parent_id' => 7,
            'name'      => 'Periode Penilaian KPI',
            'order'     => 62,
            'updated_at'=> now(),
        ]);

    echo "KPI Master menus successfully moved to Parent 7 (Master Data)!\n";
} else {
    echo "Master Data parent menu not found.\n";
}
