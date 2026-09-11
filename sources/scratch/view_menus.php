<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$menus = DB::table('system_menu_sidebars')->select('id', 'id_parent', 'name', 'url', 'order_no')->orderBy('order_no')->get();
foreach($menus as $m) {
    echo "ID: {$m->id} | Parent: {$m->id_parent} | Name: {$m->name} | URL: {$m->url} | Order: {$m->order_no}\n";
}
