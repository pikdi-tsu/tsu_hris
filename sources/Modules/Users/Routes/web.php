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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User
    Route::middleware(['permission:users:user:view'])->group(function() {
        Route::get('/json', [UserController::class, 'datatable'])->name('json');
        Route::post('/sync', [UserController::class, 'sync'])->name('sync'); // Route Sync
        Route::resource('user', UserController::class);
    });

    Route::get('/cuti_index', [CutiController::class, 'index'])->name('cuti_index');

    // Route Hari Libur
    Route::prefix('hari-libur')->name('hari-libur.')->group(function () {
        Route::get('/json', [DashboardController::class, 'getHolidays'])->name('json');
    });


    // Profile & Password
    Route::prefix('profile')->middleware(['auth'])->name('profile.')->group(function() {
        Route::get('/', [UserProfileController::class, 'index'])->name('index');
        Route::post('/profile/photo', [UserProfileController::class, 'updatePhoto'])->name('save.change-profile');
        Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('update-password');
    });
});
