<?php

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

use Modules\Admin\Http\Controllers\DashboardController;
use Modules\Admin\Http\Controllers\DataKaryawanController;
use Modules\Admin\Http\Controllers\MasterHariLiburController;
use Modules\System\Http\Middleware\CheckAdminRole;
use Illuminate\Support\Facades\Route;

// Aktifkan CheckAdminRole::class di middleware jika ada dashboard users sendiri
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- ROUTE DATA KARYAWAN ---
    Route::middleware(['permission:admin:data-karyawan:view'])->group(function () {
        Route::prefix('data-karyawan')->name('data-karyawan.')->group(function () {
            // Route JSON
            Route::get('/json', [DataKaryawanController::class, 'datatable'])->name('json');

            // Route CRUD
            Route::resource('/', DataKaryawanController::class)->parameters(['' => 'id']);
            Route::post('/{id}/bio-aktif', [DataKaryawanController::class, 'bioAktif'])->name('bio-aktif');
        });
    });


    // --- ROUTE MASTER HARI LIBUR ---
    Route::middleware(['permission:admin:hari-libur:view'])->group(function () {
        Route::prefix('hari-libur')->name('hari-libur.')->group(function () {
            // Route JSON
            Route::get('/json', [MasterHariLiburController::class, 'datatable'])->name('json');

            // Route Sync API
            Route::get('/sync-form', [MasterHariLiburController::class, 'syncForm'])->name('sync-form');
            Route::post('/sync', [MasterHariLiburController::class, 'syncApi'])->name('sync');

            // Route CRUD
            Route::resource('/', MasterHariLiburController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER LEMBUR ---
    Route::middleware(['permission:admin:master-lembur:view'])->group(function () {
        Route::prefix('master-lembur')->name('master-lembur.')->group(function () {
            Route::get('/json', [\Modules\Admin\Http\Controllers\MasterLemburController::class, 'datatable'])->name('json');
            Route::resource('/', \Modules\Admin\Http\Controllers\MasterLemburController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER CUTI ---
    Route::middleware(['permission:admin:master-cuti:view'])->group(function () {
        Route::prefix('master-cuti')->name('master-cuti.')->group(function () {
            Route::get('/json', [\Modules\Admin\Http\Controllers\MasterCutiController::class, 'datatable'])->name('json');
            Route::resource('/', \Modules\Admin\Http\Controllers\MasterCutiController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });
});
