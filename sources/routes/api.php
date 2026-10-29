<?php

use App\Http\Controllers\Api\KaryawanSyncApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// --- API SINKRONISASI KARYAWAN UNTUK MODUL DOWNSTREAM (TSU ECOSYSTEM) ---
Route::prefix('v1/karyawan')->group(function () {
    Route::get('/sync-list', [KaryawanSyncApiController::class, 'index'])->name('api.karyawan.sync-list');
});
