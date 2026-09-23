<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$menus = Modules\System\Models\MenuSidebar::where('name', 'like', '%mpp%')
    ->orWhere('name', 'like', '%kuota%')
    ->orWhere('name', 'like', '%unit%')
    ->orWhere('route', 'like', '%mpp%')
    ->orWhere('name', 'like', '%power%')
    ->get(['id', 'parent_id', 'name', 'route', 'permission_name', 'order', 'isactive']);

foreach ($menus as $m) {
    $parentName = $m->parent_id ? Modules\System\Models\MenuSidebar::find($m->parent_id)?->name : 'ROOT';
    echo "ID: {$m->id} | Name: {$m->name} | Route: {$m->route} | Perm: {$m->permission_name} | Parent: {$parentName} ({$m->parent_id}) | Active: {$m->isactive}\n";
}
