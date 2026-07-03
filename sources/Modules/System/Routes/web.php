<?php

use App\Http\Controllers\EmergencyLoginController;
use App\Http\Controllers\SsoController;
use Illuminate\Support\Facades\Route;
use Modules\System\Http\Controllers\HomeController;
use Modules\System\Http\Controllers\LoginController;
use Modules\System\Http\Controllers\MenuController;
use Modules\System\Http\Controllers\PermissionController;
use Modules\System\Http\Controllers\RoleController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

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

Route::prefix('')->group(function() {

    Route::get('/', [HomeController::class, 'index'])->name('indexing')->middleware('web', 'guest');

    Route::middleware(['web'])->group(function () {
        Route::get('login', [LoginController::class, 'index'])->name('login')->middleware('guest');
        Route::post('login', [LoginController::class, 'login'])->name('login.action');
        Route::get('login/sso', [SsoController::class, 'redirect'])->name('sso.login');
        Route::get('login/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');
        Route::get('emergency-login', [EmergencyLoginController::class, 'login'])->name('emergency-login');
        Route::get('rescue-login', [EmergencyLoginController::class, 'showRescueForm'])->name('rescue');
        Route::post('rescue-login', [EmergencyLoginController::class, 'processRescueLogin'])->name('rescue.post');
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');

        // System Navigation
        Route::prefix('system')->middleware(['auth'])->name('system.')->group(function() {
            // Role
            Route::middleware(['permission:system:role:view'])->group(function() {
                Route::get('role/json', [RoleController::class, 'datatable'])->name('role.json');
                Route::post('role/sync', [RoleController::class, 'sync'])->name('role.sync'); // Route Sync
                Route::resource('role', RoleController::class);
            });

            // Permissions
            Route::middleware(['permission:system:permission:view'])->group(function() {
                Route::get('permission/json', [PermissionController::class, 'datatable'])->name('permission.json');
                Route::resource('permission', PermissionController::class)->except(['create', 'edit', 'show']);
            });

            // Menu
            Route::middleware(['permission:system:menu:view'])->group(function() {
                Route::get('menu/json', [MenuController::class, 'datatable'])->name('menu.json');
                Route::resource('menu', MenuController::class);
            });
        });

        // Notifications
        Route::prefix('notifications')->middleware(['auth'])->name('users.notifications.')->group(function() {
            Route::get('/', [\Modules\System\Http\Controllers\NotificationController::class, 'index'])->name('index');
            Route::get('/read/{id}', [\Modules\System\Http\Controllers\NotificationController::class, 'read'])->name('read');
            Route::post('/read-all', [\Modules\System\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('readAll');
            Route::get('/backup-clear', [\Modules\System\Http\Controllers\NotificationController::class, 'backupAndClear'])->name('backupClear');
        });

        // Custom Route View File
        Route::middleware(['auth'])->get('/storage/exports/{filename}', function ($filename) {
            $path = storage_path('app/public/exports/' . $filename);
            
            if (!File::exists($path)) {
                abort(404);
            }
            
            return Response::download($path);
        });
        
        Route::middleware(['auth'])->get('/storage/lembur/bukti/{filename}', function ($filename) {
            $path = storage_path('app/public/lembur/bukti/' . $filename);
            
            if (!File::exists($path)) {
                abort(404);
            }
            
            return Response::file($path);
        });
    });
});
