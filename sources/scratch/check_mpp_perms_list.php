<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$perms = Spatie\Permission\Models\Permission::where('name', 'like', '%mpp%')->get();
foreach ($perms as $p) {
    echo "ID: {$p->id} | Name: {$p->name} | Guard: {$p->guard_name}\n";
}
