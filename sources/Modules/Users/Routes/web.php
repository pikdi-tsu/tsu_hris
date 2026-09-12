<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\ApprovalCutiController;
use Modules\Users\Http\Controllers\ApprovalIzinController;
use Modules\Users\Http\Controllers\ApprovalLemburController;
use Modules\Users\Http\Controllers\MahasiswaController;
use Modules\Users\Http\Controllers\PegawaiController;
use Modules\Users\Http\Controllers\SelfService\CutiController;
use Modules\Users\Http\Controllers\SelfService\DashboardController;
use Modules\Users\Http\Controllers\SelfService\IzinController;
use Modules\Users\Http\Controllers\SelfService\LemburController;
use Modules\Users\Http\Controllers\UserController;
use Modules\Users\Http\Controllers\UserProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('users')->name('users.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User
    Route::middleware(['permission:users:user:view'])->group(function() {
        Route::get('users/json', [UserController::class, 'datatable'])->name('user.json');
        Route::get('user/json', [UserController::class, 'datatable'])->name('json'); // Alias users.json
        Route::post('user/sync', [UserController::class, 'sync'])->name('user.sync'); // Route Sync
        Route::post('users/sync', [UserController::class, 'sync'])->name('sync'); // Alias Route Sync
        Route::resource('user', UserController::class);
    });

    //Cuti
    Route::prefix('cuti')->name('cuti.')->group(function () {
        Route::get('/', [CutiController::class, 'index'])->name('index');
        Route::post('/simpan', [CutiController::class, 'simpan'])->name('simpan');
        Route::post('/datatables', [CutiController::class, 'datatables'])->name('datatables');
        Route::post('/edit', [CutiController::class, 'edit'])->name('edit');
        Route::post('/detail', [CutiController::class, 'detail'])->name('detail');
        Route::get('/{id}/bukti-file', [CutiController::class, 'streamBukti'])->name('stream-bukti');
    });

    //Izin
    Route::prefix('izin')->name('izin.')->group(function () {
        Route::get('/', [IzinController::class, 'index'])->name('index');
        Route::post('/simpan', [IzinController::class, 'simpan'])->name('simpan');
        Route::post('/datatables', [IzinController::class, 'datatables'])->name('datatables');
        Route::post('/edit', [IzinController::class, 'edit'])->name('edit');
        Route::post('/detail', [IzinController::class, 'detail'])->name('detail');
        Route::get('/{id}/bukti-file', [IzinController::class, 'streamBukti'])->name('stream-bukti');
    });

    // Route Hari Libur (Kalender Dashboard)
    Route::prefix('hari-libur')->name('hari-libur.')->group(function () {
        Route::get('/json', [DashboardController::class, 'getHolidays'])->name('json');
    });

    // Lembur
    Route::middleware(['permission:users:lembur:view'])->group(function() {
        Route::prefix('lembur')->name('lembur.')->group(function () {
            Route::get('/json', [LemburController::class, 'datatable'])->name('json');
            Route::get('/', [LemburController::class, 'index'])->name('index');
            Route::post('/', [LemburController::class, 'store'])->name('store');
            Route::post('/{id}/tarik', [LemburController::class, 'tarik'])->name('tarik');
            Route::get('/{id}/edit', [LemburController::class, 'edit'])->name('edit');
            Route::put('/{id}', [LemburController::class, 'update'])->name('update');
            Route::delete('/{id}', [LemburController::class, 'destroy'])->name('destroy');
            Route::get('/{id}', [LemburController::class, 'show'])->name('show');
        });
    });

    // Profile & Password
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [UserProfileController::class, 'index'])->name('index');
        Route::post('/profile/photo', [UserProfileController::class, 'updatePhoto'])->name('save.change-profile');
        Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('update-password');
    });

    // Approval Cuti
    Route::prefix('approval-cuti')->name('approval-cuti.')->group(function () {
        Route::get('/', [ApprovalCutiController::class, 'index'])->name('index');
        Route::post('/datatables', [ApprovalCutiController::class, 'datatables'])->name('datatables');
        Route::post('/detail', [ApprovalCutiController::class, 'detail'])->name('detail');
        Route::post('/simpan', [ApprovalCutiController::class, 'simpan'])->name('simpan');
    });

    // Approval Izin
    Route::prefix('approval-izin')->name('approval-izin.')->group(function () {
        Route::get('/', [ApprovalIzinController::class, 'index'])->name('index');
        Route::post('/datatables', [ApprovalIzinController::class, 'datatables'])->name('datatables');
        Route::post('/detail', [ApprovalIzinController::class, 'detail'])->name('detail');
        Route::post('/simpan', [ApprovalIzinController::class, 'simpan'])->name('simpan');
    });

    // Approval Lembur
    Route::prefix('approval-lembur')->name('approval-lembur.')->group(function () {
        Route::get('/', [ApprovalLemburController::class, 'index'])->name('index');
        Route::post('/datatables', [ApprovalLemburController::class, 'datatables'])->name('datatables');
        Route::post('/detail', [ApprovalLemburController::class, 'detail'])->name('detail');
        Route::post('/simpan', [ApprovalLemburController::class, 'simpan'])->name('simpan');
    });

    // Legacy Route Fallbacks (Deprecated)
    Route::get('/indexapprovalcuti', fn() => redirect()->route('users.approval-cuti.index'))->name('indexapprovalcuti');
    Route::get('/indexapprovalizin', fn() => redirect()->route('users.approval-izin.index'))->name('indexapprovalizin');
    Route::get('/indexapprovallembur', fn() => redirect()->route('users.approval-lembur.index'))->name('indexapprovallembur');

    // MPP (Manpower Planning)
    Route::prefix('mpp')->name('mpp.')->group(function () {
        Route::get('/', [\Modules\Users\Http\Controllers\SelfService\MppController::class, 'index'])->name('index');
        Route::post('/datatables', [\Modules\Users\Http\Controllers\SelfService\MppController::class, 'datatables'])->name('datatables');
        Route::post('/simpan', [\Modules\Users\Http\Controllers\SelfService\MppController::class, 'simpan'])->name('simpan');
        Route::post('/detail', [\Modules\Users\Http\Controllers\SelfService\MppController::class, 'detail'])->name('detail');
    });

    // BKD (Beban Kinerja Dosen Mandiri)
    Route::prefix('bkd')->name('bkd.')->group(function () {
        Route::get('/', [\Modules\Users\Http\Controllers\SelfService\LaporanBkdUserController::class, 'index'])->name('index');
        Route::post('/store', [\Modules\Users\Http\Controllers\SelfService\LaporanBkdUserController::class, 'store'])->name('store');
        Route::get('/{id}/stream', [\Modules\Users\Http\Controllers\SelfService\LaporanBkdUserController::class, 'stream'])->name('stream');
    });
});
