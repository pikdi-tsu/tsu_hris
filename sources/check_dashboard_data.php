<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RkatPeriode;
use App\Models\RkatPengajuan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$admin = User::whereHas('roles', fn($q) => $q->where('name', 'super admin hris'))->first();
Auth::login($admin);

$controller = app(\Modules\Admin\Http\Controllers\Rkat\RkatDashboardController::class);

echo "--- PERIODES ---\n";
foreach (RkatPeriode::all() as $p) {
    echo "ID: {$p->id} | Tahun: {$p->tahun_anggaran} | Status: {$p->status} | Pengajuan: " . $p->pengajuans()->count() . "\n";
}

echo "\n--- DEFAULT DASHBOARD REQUEST ---\n";
$view = $controller->index(new Request());
$data = $view->getData();
echo "Selected Periode: " . $data['selectedPeriodeId'] . " (Tahun: " . optional($data['selectedPeriode'])->tahun_anggaran . ")\n";
echo "Total Pengajuan: " . $data['totalPengajuan'] . "\n";
echo "Total Disetujui: " . $data['totalDisetujui'] . "\n";
echo "Program Chart Count: " . count($data['programChart']) . "\n";
print_r($data['programChart']);
echo "Unit Chart Count: " . count($data['unitChart']) . "\n";
print_r($data['unitChart']);
