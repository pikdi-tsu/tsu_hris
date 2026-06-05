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
use \Modules\Admin\Http\Controllers\MasterLemburController;
use Modules\Admin\Http\Controllers\MasterJabatanController;
use Modules\Admin\Http\Controllers\RiwayatJabatanController;
use Illuminate\Support\Facades\Route;

// Aktifkan CheckAdminRole::class di middleware jika ada dashboard users sendiri
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- ROUTE DATA KARYAWAN ---
    Route::middleware(['permission:admin:data-karyawan:view'])->group(function () {
        Route::prefix('data-karyawan')->name('data-karyawan.')->group(function () {
            // Route JSON
            Route::get('/json', [DataKaryawanController::class, 'datatable'])->name('json');

            // Route Mutasi Jabatan
            Route::get('/{id}/mutasi', [DataKaryawanController::class, 'mutasiModal'])->name('mutasi');
            Route::post('/{id}/mutasi', [DataKaryawanController::class, 'storeMutasi'])->name('store-mutasi');

            // Route Kelola Fungsional
            Route::get('/{id}/fungsional', [DataKaryawanController::class, 'kelolaFungsionalModal'])->name('kelola-fungsional');
            Route::post('/{id}/fungsional', [DataKaryawanController::class, 'storeFungsional'])->name('store-fungsional');
            Route::delete('/fungsional/{fungsional_id}', [DataKaryawanController::class, 'destroyFungsional'])->name('destroy-fungsional');

            // Route Kelola Struktural
            Route::get('/{id}/struktural', [DataKaryawanController::class, 'kelolaStrukturalModal'])->name('kelola-struktural');
            Route::post('/{id}/struktural', [DataKaryawanController::class, 'storeStruktural'])->name('store-struktural');
            Route::delete('/struktural/{struktural_id}', [DataKaryawanController::class, 'destroyStruktural'])->name('destroy-struktural');

            // Route Riwayat Jabatan (Read Only)
            Route::get('/{id}/riwayat', [DataKaryawanController::class, 'riwayatModal'])->name('riwayat');
            Route::get('/{id}/export-riwayat', [DataKaryawanController::class, 'exportRiwayatExcel'])->name('export-riwayat');

            // Route CRUD
            Route::resource('/', DataKaryawanController::class)->parameters(['' => 'id']);
            Route::post('/{id}/bio-aktif', [DataKaryawanController::class, 'bioAktif'])->name('bio-aktif');
        });
    });

    // --- ROUTE RIWAYAT JABATAN MENU ---
    Route::middleware(['permission:admin:riwayat-jabatan:view'])->group(function () {
        Route::prefix('riwayat-jabatan')->name('riwayat-jabatan.')->group(function () {
            Route::get('/', [RiwayatJabatanController::class, 'index'])->name('index');
            Route::get('/json', [RiwayatJabatanController::class, 'datatable'])->name('json');
            Route::get('/export', [RiwayatJabatanController::class, 'exportGlobal'])->name('export');

            Route::get('/{id}/edit', [RiwayatJabatanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RiwayatJabatanController::class, 'update'])->name('update');
            Route::delete('/{id}', [RiwayatJabatanController::class, 'destroy'])->name('destroy');
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
            Route::get('/json', [MasterLemburController::class, 'datatable'])->name('json');
            Route::resource('/', MasterLemburController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER JABATAN ---
    Route::middleware(['permission:admin:master-jabatan:view'])->group(function () {
        Route::prefix('master-jabatan')->name('master-jabatan.')->group(function () {
            Route::get('/', [MasterJabatanController::class, 'index'])->name('index');

            // Struktural
            Route::prefix('struktural')->name('struktural.')->group(function () {
                Route::get('/json', [MasterJabatanController::class, 'datatableStruktural'])->name('json');
                Route::get('/create', [MasterJabatanController::class, 'createStruktural'])->name('create');
                Route::post('/store', [MasterJabatanController::class, 'storeStruktural'])->name('store');
                Route::get('/{id}/edit', [MasterJabatanController::class, 'editStruktural'])->name('edit');
                Route::put('/{id}', [MasterJabatanController::class, 'updateStruktural'])->name('update');
                Route::delete('/{id}', [MasterJabatanController::class, 'destroyStruktural'])->name('destroy');
            });

            // Fungsional
            Route::prefix('fungsional')->name('fungsional.')->group(function () {
                Route::get('/json', [MasterJabatanController::class, 'datatableFungsional'])->name('json');
                Route::get('/create', [MasterJabatanController::class, 'createFungsional'])->name('create');
                Route::post('/store', [MasterJabatanController::class, 'storeFungsional'])->name('store');
                Route::get('/{id}/edit', [MasterJabatanController::class, 'editFungsional'])->name('edit');
                Route::put('/{id}', [MasterJabatanController::class, 'updateFungsional'])->name('update');
                Route::delete('/{id}', [MasterJabatanController::class, 'destroyFungsional'])->name('destroy');
            });

            // Pangkat Golongan
            Route::prefix('pangkat')->name('pangkat.')->group(function () {
                Route::get('/json', [MasterJabatanController::class, 'datatablePangkat'])->name('json');
                Route::get('/create', [MasterJabatanController::class, 'createPangkat'])->name('create');
                Route::post('/store', [MasterJabatanController::class, 'storePangkat'])->name('store');
                Route::get('/{id}/edit', [MasterJabatanController::class, 'editPangkat'])->name('edit');
                Route::put('/{id}', [MasterJabatanController::class, 'updatePangkat'])->name('update');
                Route::delete('/{id}', [MasterJabatanController::class, 'destroyPangkat'])->name('destroy');
            });
        });
    });

    // --- ROUTE MASTER CUTI ---
    Route::middleware(['permission:admin:master-cuti:view'])->group(function () {
        Route::prefix('master-cuti')->name('master-cuti.')->group(function () {
            Route::get('/json', [\Modules\Admin\Http\Controllers\MasterCutiController::class, 'datatable'])->name('json');
            Route::resource('/', \Modules\Admin\Http\Controllers\MasterCutiController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });

    // --- ROUTE MASTER IZIN ---
    Route::middleware(['permission:admin:master-izin:view'])->group(function () {
        Route::prefix('master-izin')->name('master-izin.')->group(function () {
            Route::get('/json', [\Modules\Admin\Http\Controllers\MasterIzinController::class, 'datatable'])->name('json');
            Route::resource('/', \Modules\Admin\Http\Controllers\MasterIzinController::class)->parameters(['' => 'id'])->except(['show']);
        });
    });
});
