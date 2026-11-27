<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\RkatPeriode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$admin = User::whereHas('roles', fn($q) => $q->where('name', 'super admin hris'))->first();
Auth::login($admin);

$controller = app(\Modules\Admin\Http\Controllers\Rkat\RkatDashboardController::class);

$p2026 = RkatPeriode::where('tahun_anggaran', '2026')->first();
$req = new Request(['periode_id' => $p2026->id]);
$view = $controller->index($req);
$data = $view->getData();

echo "Selected Periode: " . $data['selectedPeriodeId'] . " (Tahun: " . optional($data['selectedPeriode'])->tahun_anggaran . ")\n";
echo "Total Pengajuan: " . $data['totalPengajuan'] . "\n";
echo "Total Disetujui: " . $data['totalDisetujui'] . "\n";
echo "Program Chart Count: " . count($data['programChart']) . "\n";
print_r($data['programChart']);
echo "Unit Chart Count: " . count($data['unitChart']) . "\n";
print_r($data['unitChart']);
