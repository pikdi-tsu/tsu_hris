<?php
require 'd:/htdocs/tsu_hris/sources/vendor/autoload.php';
$app = require_once 'd:/htdocs/tsu_hris/sources/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KpiPeriode;
use App\Models\KpiMasterPerspektif;
use App\Models\KpiMasterIndikator;
use App\Models\KpiUnitIndikator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

echo "====================================================\n";
echo "       AUTOMATED VERIFICATION TEST: KPI SYSTEM       \n";
echo "====================================================\n\n";

$passCount = 0;
$totalTests = 0;

function assertTest($condition, $testName) {
    global $passCount, $totalTests;
    $totalTests++;
    if ($condition) {
        $passCount++;
        echo "[PASS] {$testName}\n";
    } else {
        echo "[FAIL] {$testName}\n";
    }
}

// 1. Check Tables & Record Counts
echo "--- 1. DATABASE & SEEDING RECORD COUNTS ---\n";
$perspektifCount = KpiMasterPerspektif::count();
assertTest($perspektifCount === 4, "4 Perspektif Balanced Scorecard (FIN, CUS, INT, LRN) exist (Found: {$perspektifCount})");

$periodeCount = KpiPeriode::count();
assertTest($periodeCount >= 1, "Periode 2026 exists (Found: {$periodeCount})");

$masterIndikatorCount = KpiMasterIndikator::count();
assertTest($masterIndikatorCount >= 10, "Master Indikator Kamus records exist (Found: {$masterIndikatorCount})");

$unitIndikatorCount = KpiUnitIndikator::count();
assertTest($unitIndikatorCount >= 5, "Cascading Unit Indikator records exist (Found: {$unitIndikatorCount})");

// 2. Test Approach 1 Hierarchy (Parent & Sub Indikators)
echo "\n--- 2. TEST APPROACH 1 HIERARCHY (PARENT & SUB-INDIKATOR) ---\n";
$magangInduk = KpiMasterIndikator::where('kode_indikator', 'INT-01')->first();
assertTest($magangInduk !== null && $magangInduk->level === 'induk', "Indikator Induk INT-01 exists with level 'induk'");

$magangSub = KpiMasterIndikator::where('kode_indikator', 'INT-01a')->first();
assertTest($magangSub !== null && $magangSub->level === 'sub', "Sub-Indikator INT-01a exists with level 'sub'");

if ($magangInduk && $magangSub) {
    assertTest($magangSub->parent_id === $magangInduk->id, "Sub-indikator parent_id matches Induk id");
    assertTest($magangInduk->subIndikators->contains('id', $magangSub->id), "Induk subIndikators relation correctly returns sub-indikator child");
    assertTest($magangSub->parent->id === $magangInduk->id, "Sub-indikator parent relation correctly returns parent induk");
}

// 3. Test Cascading Tree Relation (Pimpinan -> Unit Pelaksana)
echo "\n--- 3. TEST CASCADING TREE RELATION ---\n";
$childCascaded = KpiUnitIndikator::whereNotNull('parent_unit_indikator_id')->first();
assertTest($childCascaded !== null, "Found cascaded unit indicator with parent_unit_indikator_id");
if ($childCascaded) {
    $parentIndicator = $childCascaded->parentUnitIndikator;
    assertTest($parentIndicator !== null, "Parent unit indicator relation resolves properly (Pimpinan: " . optional($parentIndicator->unit)->nama_unit . ")");
    assertTest($parentIndicator->childUnitIndikators->contains('id', $childCascaded->id), "Parent childUnitIndikators collection contains child unit indicator");
}

// 4. Test BSC Calculation Formulas (Maximize & Minimize)
echo "\n--- 4. TEST BSC SCORING FORMULA ---\n";
// Test Maximize: Target 80, Realisasi 88, Bobot 20%
$dummyMax = new KpiUnitIndikator([
    'target_angka'    => 80,
    'realisasi_angka' => 88,
    'bobot'           => 20,
]);
$dummyMasterMax = new KpiMasterIndikator(['polaritas' => 'Maximize']);
$dummyMax->setRelation('masterIndikator', $dummyMasterMax);
$dummyMax->hitungCapaianDanSkor();

assertTest(round($dummyMax->capaian_persen, 2) == 110.00, "Maximize Capaian%: Expected 110.00%, Got: " . $dummyMax->capaian_persen . "%");
assertTest(round($dummyMax->skor, 2) == 22.00, "Maximize Skor: Expected 22.00, Got: " . $dummyMax->skor);

// Test Minimize: Target 3 hari, Realisasi 2 hari (faster is better), Bobot 10%
$dummyMin = new KpiUnitIndikator([
    'target_angka'    => 3,
    'realisasi_angka' => 2,
    'bobot'           => 10,
]);
$dummyMasterMin = new KpiMasterIndikator(['polaritas' => 'Minimize']);
$dummyMin->setRelation('masterIndikator', $dummyMasterMin);
$dummyMin->hitungCapaianDanSkor();

assertTest(round($dummyMin->capaian_persen, 2) == 150.00, "Minimize Capaian%: Expected 150.00%, Got: " . $dummyMin->capaian_persen . "%");
assertTest(round($dummyMin->skor, 2) == 15.00, "Minimize Skor: Expected 15.00, Got: " . $dummyMin->skor);

// 5. Test Controller & View Rendering
echo "\n--- 5. TEST CONTROLLER, DATATABLES & VIEW COMPILATION ---\n";
// Log in as Super Admin
$user = User::first();
if ($user) {
    Auth::login($user);
}
view()->share('errors', new \Illuminate\Support\ViewErrorBag());

$periode = KpiPeriode::first();
$unit = App\Models\MasterUnit::first();

// A. Test KpiDashboardController
try {
    $c = app()->make(Modules\Admin\Http\Controllers\KpiDashboardController::class);
    $view = $c->index(new Request());
    $renderedHtml = $view->render();
    assertTest(strlen($renderedHtml) > 500 && str_contains($renderedHtml, 'Balanced Scorecard'), "KpiDashboardController@index renders successfully with HTML (" . strlen($renderedHtml) . " bytes)");
} catch (\Exception $e) {
    assertTest(false, "KpiDashboardController@index failed: " . $e->getMessage());
}

// B. Test KpiPeriodeController
try {
    $c = app()->make(Modules\Admin\Http\Controllers\KpiPeriodeController::class);
    $view = $c->index();
    $renderedHtml = $view->render();
    assertTest(strlen($renderedHtml) > 500 && str_contains($renderedHtml, 'Master Periode Penilaian'), "KpiPeriodeController@index renders successfully with HTML (" . strlen($renderedHtml) . " bytes)");

    $dt = $c->dataTable();
    $dtData = $dt->getData(true);
    assertTest(isset($dtData['data']) && count($dtData['data']) >= 1, "KpiPeriodeController@dataTable returns valid DataTables JSON (Count: " . count($dtData['data']) . ")");
} catch (\Exception $e) {
    assertTest(false, "KpiPeriodeController failed: " . $e->getMessage());
}

// C. Test KpiMasterIndikatorController
try {
    $c = app()->make(Modules\Admin\Http\Controllers\KpiMasterIndikatorController::class);
    $view = $c->index(new Request());
    $renderedHtml = $view->render();
    assertTest(strlen($renderedHtml) > 500 && str_contains($renderedHtml, 'Kamus Master Indikator'), "KpiMasterIndikatorController@index renders successfully with HTML (" . strlen($renderedHtml) . " bytes)");

    $dt = $c->dataTable(new Request());
    $dtData = $dt->getData(true);
    assertTest(isset($dtData['data']) && count($dtData['data']) >= 10, "KpiMasterIndikatorController@dataTable returns valid DataTables JSON (Count: " . count($dtData['data']) . ")");
} catch (\Exception $e) {
    assertTest(false, "KpiMasterIndikatorController failed: " . $e->getMessage());
}

// D. Test KpiCascadingController
try {
    $c = app()->make(Modules\Admin\Http\Controllers\KpiCascadingController::class);
    $req = new Request(['periode_id' => $periode->id, 'unit_id' => $unit->id]);
    $view = $c->index($req);
    $renderedHtml = $view->render();
    assertTest(strlen($renderedHtml) > 500 && str_contains($renderedHtml, 'Cascading KPI Unit Kerja'), "KpiCascadingController@index renders successfully with HTML (" . strlen($renderedHtml) . " bytes)");

    $dt = $c->dataTable(new Request(['periode_id' => $periode->id, 'master_unit_id' => $unit->id]));
    $dtData = $dt->getData(true);
    assertTest(isset($dtData['data']), "KpiCascadingController@dataTable returns valid DataTables JSON (Count: " . count($dtData['data']) . ")");
} catch (\Exception $e) {
    assertTest(false, "KpiCascadingController failed: " . $e->getMessage());
}

// E. Test KpiMonitoringController
try {
    $c = app()->make(Modules\Admin\Http\Controllers\KpiMonitoringController::class);
    $req = new Request(['periode_id' => $periode->id, 'unit_id' => $unit->id]);
    $view = $c->index($req);
    $renderedHtml = $view->render();
    assertTest(strlen($renderedHtml) > 500 && str_contains($renderedHtml, 'Monitoring'), "KpiMonitoringController@index renders successfully with HTML (" . strlen($renderedHtml) . " bytes)");

    $dt = $c->dataTable(new Request(['periode_id' => $periode->id, 'master_unit_id' => $unit->id]));
    $dtData = $dt->getData(true);
    assertTest(isset($dtData['data']), "KpiMonitoringController@dataTable returns valid DataTables JSON (Count: " . count($dtData['data']) . ")");
} catch (\Exception $e) {
    assertTest(false, "KpiMonitoringController failed: " . $e->getMessage());
}

// F. Test KpiMasterPerspektifController
try {
    $c = app()->make(Modules\Admin\Http\Controllers\KpiMasterPerspektifController::class);
    $view = $c->index();
    $renderedHtml = $view->render();
    assertTest(strlen($renderedHtml) > 500 && str_contains($renderedHtml, 'Master Perspektif Balanced Scorecard'), "KpiMasterPerspektifController@index renders successfully with HTML (" . strlen($renderedHtml) . " bytes)");

    $dt = $c->dataTable();
    $dtData = $dt->getData(true);
    assertTest(isset($dtData['data']) && count($dtData['data']) === 4, "KpiMasterPerspektifController@dataTable returns valid DataTables JSON (Count: " . count($dtData['data']) . ")");
} catch (\Exception $e) {
    assertTest(false, "KpiMasterPerspektifController failed: " . $e->getMessage());
}

echo "\n====================================================\n";
echo "TEST RESULTS: {$passCount} / {$totalTests} PASSED\n";
if ($passCount === $totalTests) {
    echo "ALL TESTS PASSED! KPI & Balanced Scorecard Module is 100% Operational.\n";
} else {
    echo "SOME TESTS FAILED! Please inspect errors above.\n";
}
echo "====================================================\n";
