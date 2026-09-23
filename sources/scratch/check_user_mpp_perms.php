<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== USERS AND ROLES ===\n";
$users = App\Models\User::all();
foreach ($users as $u) {
    $roles = $u->roles->pluck('name')->implode(', ');
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Roles: [{$roles}]\n";
}

echo "\n=== PERMISSIONS FOR ADMIN:MPP:VIEW & USERS:MPP:VIEW ===\n";
$roles = Spatie\Permission\Models\Role::all();
foreach ($roles as $r) {
    $hasAdminMpp = $r->hasPermissionTo('admin:mpp:view') ? 'YES' : 'NO';
    $hasUsersMpp = $r->hasPermissionTo('users:mpp:view') ? 'YES' : 'NO';
    echo "Role: {$r->name} | admin:mpp:view: {$hasAdminMpp} | users:mpp:view: {$hasUsersMpp}\n";
}
