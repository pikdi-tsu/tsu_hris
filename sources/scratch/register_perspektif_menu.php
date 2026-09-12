<?php
require 'd:/htdocs/tsu_hris/sources/vendor/autoload.php';
$app = require_once 'd:/htdocs/tsu_hris/sources/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$parent = DB::table('system_menu_sidebars')->where('name', 'KPI & Balanced Scorecard')->first();
if ($parent) {
    DB::table('system_menu_sidebars')->updateOrInsert(
        ['route' => 'admin.kpi.perspektif.index'],
        [
            'parent_id'       => $parent->id,
            'name'            => 'Perspektif BSC',
            'icon'            => 'fas fa-layer-group',
            'type'            => 'item',
            'permission_name' => 'admin:kpi:view',
            'order'           => 5,
            'isactive'        => 1,
            'updated_at'      => now(),
            'created_at'      => now(),
        ]
    );

    DB::table('system_menu_sidebars')->where('route', 'admin.kpi.periode.index')->update(['order' => 6]);
    echo "Perspektif BSC submenu registered successfully!\n";
} else {
    echo "Parent menu not found.\n";
}
