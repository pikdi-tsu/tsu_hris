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
use Modules\Admin\Http\Controllers\MasterHariLiburController;
use Modules\System\Http\Middleware\CheckAdminRole;

Route::prefix('admin')->name('admin.')->middleware(['auth', CheckAdminRole::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- ROUTE MASTER HARI LIBUR ---
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
