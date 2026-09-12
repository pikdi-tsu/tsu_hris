<?php
require 'd:/htdocs/tsu_hris/sources/vendor/autoload.php';
$app = require_once 'd:/htdocs/tsu_hris/sources/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "1. Registering KPI Spatie Permissions...\n";
$kpiPerms = [
    'admin:kpi:view',
    'admin:kpi:create',
    'admin:kpi:edit',
    'admin:kpi:delete',
];

foreach ($kpiPerms as $p) {
    Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
}

$allRoles = Role::all();
echo "Available roles: " . $allRoles->pluck('name')->implode(', ') . "\n";
foreach ($allRoles as $r) {
    $r->givePermissionTo($kpiPerms);
}
echo "Permissions granted to all " . $allRoles->count() . " roles.\n";

echo "2. Registering Sidebar Menus...\n";
$parentKpi = DB::table('system_menu_sidebars')->where('name', 'KPI & Balanced Scorecard')->whereNull('parent_id')->first();
if (!$parentKpi) {
    $maxOrder = DB::table('system_menu_sidebars')->whereNull('parent_id')->max('order') ?? 10;
    $parentId = DB::table('system_menu_sidebars')->insertGetId([
        'parent_id'       => null,
        'name'            => 'KPI & Balanced Scorecard',
        'icon'            => 'fas fa-chart-line',
        'type'            => 'item',
        'route'           => '#',
        'permission_name' => 'admin:kpi:view',
        'order'           => $maxOrder + 1,
        'isactive'        => 1,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);
    echo "Parent Menu created with ID: {$parentId}\n";
} else {
    $parentId = $parentKpi->id;
    echo "Parent Menu already exists with ID: {$parentId}\n";
}

$submenus = [
    [
        'name'            => 'Dashboard KPI',
        'icon'            => 'fas fa-tachometer-alt',
        'route'           => 'admin.kpi.dashboard.index',
        'order'           => 1,
    ],
    [
        'name'            => 'Cascading KPI Unit Kerja',
        'icon'            => 'fas fa-sitemap',
        'route'           => 'admin.kpi.cascading.index',
        'order'           => 2,
    ],
    [
        'name'            => 'Monitoring & Evaluasi',
        'icon'            => 'fas fa-clipboard-check',
        'route'           => 'admin.kpi.monitoring.index',
        'order'           => 3,
    ],
    [
        'name'            => 'Kamus Indikator KPI',
        'icon'            => 'fas fa-book-reader',
        'route'           => 'admin.kpi.master-indikator.index',
        'order'           => 4,
    ],
    [
        'name'            => 'Periode Penilaian',
        'icon'            => 'fas fa-calendar-alt',
        'route'           => 'admin.kpi.periode.index',
        'order'           => 5,
    ],
];

foreach ($submenus as $sub) {
    $exists = DB::table('system_menu_sidebars')->where('route', $sub['route'])->first();
    if (!$exists) {
        DB::table('system_menu_sidebars')->insert([
            'parent_id'       => $parentId,
            'name'            => $sub['name'],
            'icon'            => $sub['icon'],
            'type'            => 'item',
            'route'           => $sub['route'],
            'permission_name' => 'admin:kpi:view',
            'order'           => $sub['order'],
            'isactive'        => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        echo "Submenu {$sub['name']} registered.\n";
    } else {
        echo "Submenu {$sub['name']} already exists.\n";
    }
}

echo "KPI Permissions and Menus successfully configured!\n";
