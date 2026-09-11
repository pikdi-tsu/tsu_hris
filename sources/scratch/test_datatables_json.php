<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Modules\Admin\Http\Controllers\SuratMasukController;
use Modules\Admin\Http\Controllers\DisposisiUnitController;
use Modules\Admin\Http\Controllers\RequestSuratController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$admin = User::where('username', 'admin_hris')->first() ?? User::first();
Auth::login($admin);

echo "Testing DataTables JSON responses:\n";

try {
    $req = Request::create('/test', 'GET');

    $smCtrl = new SuratMasukController();
    $dt1 = $smCtrl->dataTable($req);
    echo "[PASS] SuratMasukController@dataTable returned JSON successfully\n";

    $duCtrl = new DisposisiUnitController();
    $dt2 = $duCtrl->dataTable($req);
    echo "[PASS] DisposisiUnitController@dataTable returned JSON successfully\n";

    $rsCtrl = new RequestSuratController();
    $dt3 = $rsCtrl->sekretariatDataTable($req);
    echo "[PASS] RequestSuratController@sekretariatDataTable returned JSON successfully\n";

} catch (\Exception $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
