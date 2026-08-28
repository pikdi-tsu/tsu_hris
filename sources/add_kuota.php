<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \Illuminate\Support\Facades\Schema::table('master_units', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->integer('kuota_mpp')->nullable()->default(0)->after('keterangan');
    });
    echo "Column kuota_mpp added successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
