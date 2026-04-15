<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\SelfService\DashboardController;
use Modules\Users\Http\Controllers\SelfService\CutiController;
use Modules\Users\Http\Controllers\PegawaiController;
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
    // User
    Route::middleware(['permission:users:user:view'])->group(function () {
        Route::get('users/json', [UserController::class, 'datatable'])->name('user.json');
        Route::post('user/sync', [UserController::class, 'sync'])->name('user.sync'); // Route Sync
        Route::resource('user', UserController::class);
    });

    //Dashbooard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //Cuti
    Route::prefix('cuti')->name('cuti.')->group(function () {
        Route::get('/', [CutiController::class, 'index'])->name('index');
        Route::post('/simpan', [CutiController::class, 'simpan'])->name('simpan');
        Route::post('/datatables', [CutiController::class, 'datatables'])->name('datatables');
        Route::post('/edit', [CutiController::class, 'edit'])->name('edit');
        Route::post('/detail', [CutiController::class, 'detail'])->name('detail');
    });

    // Route::get('/cuti_index', [CutiController::class, 'index'])->name('cuti_index');
    // Route::post('/cuti_simpan', [CutiController::class, 'simpan'])->name('cuti_simpan');
});

// Profile & Password
Route::prefix('profile')->middleware(['auth'])->name('profile.')->group(function () {
    Route::get('/', [UserProfileController::class, 'index'])->name('index');
    Route::post('/profile/photo', [UserProfileController::class, 'updatePhoto'])->name('save.change-profile');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('update-password');
});
