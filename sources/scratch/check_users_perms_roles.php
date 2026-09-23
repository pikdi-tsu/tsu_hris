<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$superAdmin = Spatie\Permission\Models\Role::where('name', 'super admin hris')->first();
$adminTesting = Spatie\Permission\Models\Role::where('name', 'admin hris testing')->first();
$tendik = Spatie\Permission\Models\Role::where('name', 'tendik')->first();
$dosen = Spatie\Permission\Models\Role::where('name', 'dosen')->first();

echo "Super admin permissions count: " . $superAdmin->permissions()->count() . "\n";
echo "Tendik permissions count: " . $tendik->permissions()->count() . "\n";

// Check all users: permissions
$userPerms = Spatie\Permission\Models\Permission::where('name', 'like', 'users:%')->get();
echo "All users perms:\n";
foreach ($userPerms as $up) {
    $rolesWithThis = $up->roles->pluck('name')->join(', ');
    echo "{$up->name} -> [{$rolesWithThis}]\n";
}
