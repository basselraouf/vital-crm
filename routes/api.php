<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Blog\BlogCategoryController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── Auth ──────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',         [AuthController::class, 'login'])->name('auth.login');
    Route::post('logout',        [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth:api');
    Route::post('refresh-token', [AuthController::class, 'refresh'])->name('auth.refresh')->middleware('auth:api');
});

// ── Website / Public (no auth — published blogs only) ─────────────────────
Route::prefix('website')->name('website.')->group(function () {
    Route::get('blogs',              [BlogController::class, 'publicIndex'])->name('blogs.index');
    Route::get('blogs/slug/{slug}',  [BlogController::class, 'getBySlug'])->name('blogs.slug');
    Route::get('blog-categories',    [BlogCategoryController::class, 'index'])->name('blog-categories.index');
});

// ── Dashboard (auth required) ─────────────────────────────────────────────
Route::middleware('auth:api')->group(function () {

    // ── Roles & Permissions ───────────────────────────────────────────────
    Route::prefix('roles')->group(function () {
        Route::get('/',                    [RoleController::class, 'index'])->name('roles.index')->middleware('permission:view-roles');
        Route::put('{role}/permissions',   [RoleController::class, 'syncPermissions'])->name('roles.sync-permissions')->middleware('permission:edit-role');
    });

    // ── Users ─────────────────────────────────────────────────────────────
    Route::prefix('users')->group(function () {
        Route::post('/',                   [UserController::class, 'store'])->name('users.store')->middleware('permission:create-user');

        Route::prefix('{user}')->group(function () {
            Route::post('roles',           [RoleController::class, 'assignRole'])->name('users.assign-role')->middleware('permission:assign-role');
            Route::delete('roles/{role}',  [RoleController::class, 'revokeRole'])->name('users.revoke-role')->middleware('permission:assign-role');
        });
    });

    // ── Blogs ─────────────────────────────────────────────────────────────
    Route::prefix('blogs')->name('blogs.')->group(function () {
        Route::get('/',          [BlogController::class, 'index'])->name('index');
        Route::get('{id}',       [BlogController::class, 'show'])->name('show');
        Route::post('/',         [BlogController::class, 'store'])->name('store');
        Route::post('{id}',      [BlogController::class, 'update'])->name('update');  // POST for multipart/form-data
        Route::delete('{id}',    [BlogController::class, 'destroy'])->name('destroy');
    });

    // ── Blog Categories ───────────────────────────────────────────────────
    Route::prefix('blog-categories')->name('blog-categories.')->group(function () {
        Route::get('/',          [BlogCategoryController::class, 'index'])->name('index');
        Route::get('{id}',       [BlogCategoryController::class, 'show'])->name('show');
        Route::post('/',         [BlogCategoryController::class, 'store'])->name('store');
        Route::put('{id}',       [BlogCategoryController::class, 'update'])->name('update');
        Route::delete('{id}',    [BlogCategoryController::class, 'destroy'])->name('destroy');
    });

});
