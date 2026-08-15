<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Role\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth:api');
    Route::post('refresh-token', [AuthController::class, 'refresh'])->name('auth.refresh')->middleware('auth:api');
});

Route::middleware('auth:api')->group(function () {

    // Roles & Permissions
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:view-roles');
        Route::put('{role}/permissions', [RoleController::class, 'syncPermissions'])->name('roles.sync-permissions')->middleware('permission:edit-role');
    });

    Route::prefix('users/{user}')->group(function () {
        Route::post('roles', [RoleController::class, 'assignRole'])->name('users.assign-role')->middleware('permission:assign-role');
        Route::delete('roles/{role}', [RoleController::class, 'revokeRole'])->name('users.revoke-role')->middleware('permission:assign-role');
    });

});
