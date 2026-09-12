<?php

use Illuminate\Support\Facades\DB;

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Registering Persuratan & SIKD Menus ---\n";

// Update Parent Menu 52
DB::table('system_menu_sidebars')->where('id', 52)->update([
    'name' => 'Tata Persuratan & SIKD',
    'icon' => 'fas fa-mail-bulk',
    'updated_at' => now(),
]);

// Ensure submenus under 52
$submenus = [
    [
        'name'            => 'Kelola Permohonan Surat',
        'icon'            => 'fas fa-tasks',
        'type'            => 'item',
        'route'           => 'admin.request-surat.admin-index',
        'permission_name' => 'admin:persuratan-sdm:view',
        'parent_id'       => 52,
        'order'           => 1,
        'isactive'        => 1,
    ],
    [
        'name'            => 'Tugas SK Sekretariat',
        'icon'            => 'fas fa-stamp',
        'type'            => 'item',
        'route'           => 'admin.request-surat.sekretariat-inbox',
        'permission_name' => 'admin:persuratan-sdm:view',
        'parent_id'       => 52,
        'order'           => 2,
        'isactive'        => 1,
    ],
    [
        'name'            => 'Surat Masuk & SIKD',
        'icon'            => 'fas fa-inbox',
        'type'            => 'item',
        'route'           => 'admin.surat-masuk.index',
        'permission_name' => 'admin:persuratan-sdm:view',
        'parent_id'       => 52,
        'order'           => 3,
        'isactive'        => 1,
    ],
    [
        'name'            => 'Disposisi Masuk Unit',
        'icon'            => 'fas fa-paper-plane',
        'type'            => 'item',
        'route'           => 'admin.disposisi-unit.index',
        'permission_name' => null, // Accessible to all authenticated units
        'parent_id'       => 52,
        'order'           => 4,
        'isactive'        => 1,
    ],
    [
        'name'            => 'Pusat Surat Edaran & SK',
        'icon'            => 'fas fa-bullhorn',
        'type'            => 'item',
        'route'           => 'admin.surat-edaran.index',
        'permission_name' => 'admin:persuratan-sdm:view',
        'parent_id'       => 52,
        'order'           => 5,
        'isactive'        => 1,
    ],
];

foreach ($submenus as $sm) {
    $existing = DB::table('system_menu_sidebars')
        ->where('route', $sm['route'])
        ->first();

    if ($existing) {
        DB::table('system_menu_sidebars')->where('id', $existing->id)->update([
            'name'            => $sm['name'],
            'icon'            => $sm['icon'],
            'parent_id'       => $sm['parent_id'],
            'order'           => $sm['order'],
            'permission_name' => $sm['permission_name'],
            'isactive'        => 1,
            'updated_at'      => now(),
        ]);
        echo "Updated menu: {$sm['name']} (ID: {$existing->id})\n";
    } else {
        $id = DB::table('system_menu_sidebars')->insertGetId(array_merge($sm, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        echo "Inserted new menu: {$sm['name']} (ID: {$id})\n";
    }
}

echo "All menus successfully registered!\n";
