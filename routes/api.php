<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Blog\BlogCategoryController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Consultation\FreeConsultationController;
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

// ── Public API (Available to anyone, e.g. the Next.js Frontend) ───────────
Route::prefix('public')->name('public.')->group(function () {
    Route::get('blogs',              [BlogController::class, 'publicIndex'])->name('blogs.index');
    Route::get('blogs/slug/{slug}',  [BlogController::class, 'getBySlug'])->name('blogs.slug');
    Route::get('blog-categories',    [BlogCategoryController::class, 'index'])->name('blog-categories.index');

    Route::post('free-consultations', [\App\Http\Controllers\Consultation\FreeConsultationController::class, 'store'])->name('free-consultations.store');
});

// ── Dashboard (auth required) ─────────────────────────────────────────────
Route::middleware('auth:api')->group(function () {

    // ── Roles & Permissions ───────────────────────────────────────────────
    Route::get('permissions',              [RoleController::class, 'permissions'])->name('permissions.index')->middleware('permission:view-roles');

    Route::prefix('roles')->group(function () {
        Route::get('/',                    [RoleController::class, 'index'])->name('roles.index')->middleware('permission:view-roles');
        Route::post('/',                   [RoleController::class, 'store'])->name('roles.store')->middleware('permission:create-role');
        Route::put('{role}/permissions',   [RoleController::class, 'syncPermissions'])->name('roles.sync-permissions')->middleware('permission:edit-role');
        Route::delete('{role}',            [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:delete-role');
    });

    // ── Users ─────────────────────────────────────────────────────────────
    Route::prefix('users')->group(function () {
        Route::post('/',                   [UserController::class, 'store'])->name('users.store')->middleware('permission:create-user');
        Route::get('/',                    [UserController::class, 'index'])->name('users.index')->middleware('permission:view-users');
        Route::delete('{user}',            [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:delete-user');

        Route::prefix('{user}')->group(function () {
            Route::post('roles',           [RoleController::class, 'assignRole'])->name('users.assign-role')->middleware('permission:assign-role');
            Route::delete('roles/{role}',  [RoleController::class, 'revokeRole'])->name('users.revoke-role')->middleware('permission:assign-role');
        });
    });

    // ── Blogs (Dashboard) ─────────────────────────────────────────────────
    Route::prefix('blogs')->name('blogs.dashboard.')->group(function () {
        Route::get('/',          [BlogController::class, 'index'])->name('index')->middleware('permission:view-blogs');
        Route::get('{id}',       [BlogController::class, 'show'])->name('show')->middleware('permission:view-blogs');
        Route::post('/',         [BlogController::class, 'store'])->name('store')->middleware('permission:create-blog');
        Route::post('{id}',      [BlogController::class, 'update'])->name('update')->middleware('permission:edit-blog');
        Route::delete('{id}',    [BlogController::class, 'destroy'])->name('destroy')->middleware('permission:delete-blog');
    });

    // ── Blog Categories (Dashboard) ───────────────────────────────────────
    Route::prefix('blog-categories')->name('blog-categories.dashboard.')->group(function () {
        Route::get('/',          [BlogCategoryController::class, 'index'])->name('index')->middleware('permission:manage-blog-categories');
        Route::get('{id}',       [BlogCategoryController::class, 'show'])->name('show')->middleware('permission:manage-blog-categories');
        Route::post('/',         [BlogCategoryController::class, 'store'])->name('store')->middleware('permission:manage-blog-categories');
        Route::post('{id}',       [BlogCategoryController::class, 'update'])->name('update')->middleware('permission:manage-blog-categories');
        Route::delete('{id}',    [BlogCategoryController::class, 'destroy'])->name('destroy')->middleware('permission:manage-blog-categories');
    });

    // ── Free Consultations (Dashboard) ────────────────────────────────────
    Route::prefix('free-consultations')->name('free-consultations.dashboard.')->group(function () {
        Route::get('/',                 [FreeConsultationController::class, 'index'])->name('index')->middleware('permission:view-consultations');
        Route::get('{id}',              [FreeConsultationController::class, 'show'])->name('show')->middleware('permission:view-consultations');
        Route::patch('{id}/status',     [FreeConsultationController::class, 'updateStatus'])->name('update-status')->middleware('permission:edit-consultation');
        Route::delete('{id}',           [FreeConsultationController::class, 'destroy'])->name('destroy')->middleware('permission:delete-consultation');
    });
});
