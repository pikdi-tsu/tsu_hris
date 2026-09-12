<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$menus = DB::table('system_menu_sidebars')->orderBy('order')->get();
foreach($menus as $m) {
    echo "ID: {$m->id} | Parent: " . ($m->parent_id ?? 'NULL') . " | Name: {$m->name} | Route: {$m->route} | Order: {$m->order}\n";
}
