<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\SelfService\DashboardController;
use Modules\Users\Http\Controllers\SelfService\CutiController;
use Modules\Users\Http\Controllers\SelfService\IzinController;
use Modules\Users\Http\Controllers\UserController;
use Modules\Users\Http\Controllers\UserProfileController;
use Modules\Users\Http\Controllers\SelfService\LemburController;
use Modules\Users\Http\Controllers\ApprovalCutiController;

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
    Route::middleware(['permission:users:user:view'])->group(function () {
        Route::get('/json', [UserController::class, 'datatable'])->name('json');
        Route::post('/sync', [UserController::class, 'sync'])->name('sync'); // Route Sync
        Route::resource('user', UserController::class);
    });

    //Cuti
    Route::prefix('cuti')->name('cuti.')->group(function () {
        Route::get('/', [CutiController::class, 'index'])->name('index');
        Route::post('/simpan', [CutiController::class, 'simpan'])->name('simpan');
        Route::post('/datatables', [CutiController::class, 'datatables'])->name('datatables');
        Route::post('/edit', [CutiController::class, 'edit'])->name('edit');
        Route::post('/detail', [CutiController::class, 'detail'])->name('detail');
    });

    //Izin
    Route::prefix('izin')->name('izin.')->group(function () {
        Route::get('/', [IzinController::class, 'index'])->name('index');
        Route::post('/simpan', [IzinController::class, 'simpan'])->name('simpan');
        Route::post('/datatables', [IzinController::class, 'datatables'])->name('datatables');
        Route::post('/edit', [IzinController::class, 'edit'])->name('edit');
        Route::post('/detail', [IzinController::class, 'detail'])->name('detail');
    });

    // Route Hari Libur
    Route::middleware(['permission:users:hari-libur:view'])->group(function () {
        Route::prefix('hari-libur')->name('hari-libur.')->group(function () {
            Route::get('/json', [DashboardController::class, 'getHolidays'])->name('json');
        });
    });

    // Lembur
    // Route::middleware(['permission:users:lembur:view'])->group(function() {
    Route::prefix('lembur')->name('lembur.')->group(function () {
        Route::get('/json', [LemburController::class, 'datatable'])->name('json');
        Route::get('/approval/json', [LemburController::class, 'datatableApproval'])->name('approval.json');
        Route::post('/{id}/approve', [LemburController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [LemburController::class, 'reject'])->name('reject');
        Route::get('/', [LemburController::class, 'index'])->name('index');
        Route::post('/', [LemburController::class, 'store'])->name('store');
        Route::post('/{id}/tarik', [LemburController::class, 'tarik'])->name('tarik');
        Route::get('/{id}/edit', [LemburController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LemburController::class, 'update'])->name('update');
        Route::delete('/{id}', [LemburController::class, 'destroy'])->name('destroy');
        Route::get('/{id}', [LemburController::class, 'show'])->name('show');
    });
    // });

    // Profile & Password
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [UserProfileController::class, 'index'])->name('index');
        Route::post('/profile/photo', [UserProfileController::class, 'updatePhoto'])->name('save.change-profile');
        Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('update-password');
    });

    //Approval Cuti
    Route::get('/indexapprovalcuti', [ApprovalCutiController::class, 'index'])->name('indexapprovalcuti');
    Route::post('/datatablesapproval', [ApprovalCutiController::class, 'datatables'])->name('datatablesapproval');
    Route::post('/approvaldetail', [ApprovalCutiController::class, 'detail'])->name('approvaldetail');
    Route::post('/simpanapproval', [ApprovalCutiController::class, 'simpan'])->name('simpanapproval');
});
