<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Blog\BlogCategoryController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Consultation\FreeConsultationController;
use App\Http\Controllers\Accommodation\AccommodationController;
use App\Http\Controllers\Journey\JourneyController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Service\ServiceController;
use App\Http\Controllers\Service\ServiceReviewController;
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

// ── Public API (Available to anyone, e.g. the Next.js Frontend) ───────────
Route::prefix('public')->name('public.')->group(function () {
    Route::get('blogs',              [BlogController::class, 'publicIndex'])->name('blogs.index');
    Route::get('blogs/slug/{slug}',  [BlogController::class, 'getBySlug'])->name('blogs.slug');
    Route::get('blog-categories',    [BlogCategoryController::class, 'index'])->name('blog-categories.index');

    Route::post('free-consultations', [FreeConsultationController::class, 'store'])->name('free-consultations.store');

    Route::get('services',           [ServiceController::class, 'publicIndex'])->name('services.index');
    Route::get('services/{slug}',    [ServiceController::class, 'showBySlug'])->name('services.show');
    Route::post('services/{slug}/reviews', [ServiceReviewController::class, 'publicStore'])->name('services.reviews.store');

    Route::get('accommodations',           [AccommodationController::class, 'publicIndex'])->name('accommodations.index');
    Route::get('accommodations/{slug}',    [AccommodationController::class, 'publicShow'])->name('accommodations.show');

    Route::post('journey-requests',        [JourneyController::class, 'store'])->name('journey-requests.store');
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

    // ── Services (Dashboard) ────────────────────────────────────────────
    Route::prefix('services')->name('services.dashboard.')->group(function () {
        Route::get('/',     [ServiceController::class, 'index'])->name('index')->middleware('permission:view-services');
        Route::get('{id}',  [ServiceController::class, 'show'])->name('show')->middleware('permission:view-services');
        Route::post('/',    [ServiceController::class, 'store'])->name('store')->middleware('permission:create-service');
        Route::post('{id}', [ServiceController::class, 'update'])->name('update')->middleware('permission:edit-service');
        Route::delete('{id}',[ServiceController::class, 'destroy'])->name('destroy')->middleware('permission:delete-service');
        Route::post('{id}/packages',    [ServiceController::class, 'syncPackages'])->name('packages')->middleware('permission:edit-service');
        Route::post('{id}/price-items', [ServiceController::class, 'syncPriceItems'])->name('price-items')->middleware('permission:edit-service');
        Route::post('{id}/faqs',        [ServiceController::class, 'syncFaqs'])->name('faqs')->middleware('permission:edit-service');

        // Reviews
        Route::get('{id}/reviews',                                  [ServiceReviewController::class, 'index'])->name('reviews.index')->middleware('permission:view-services');
        Route::post('{id}/reviews',                                 [ServiceReviewController::class, 'store'])->name('reviews.store')->middleware('permission:edit-service');
        Route::patch('{id}/reviews/{reviewId}/status',             [ServiceReviewController::class, 'updateStatus'])->name('reviews.status')->middleware('permission:edit-service');
        Route::delete('{id}/reviews/{reviewId}',                   [ServiceReviewController::class, 'destroy'])->name('reviews.destroy')->middleware('permission:edit-service');
    });

    // ── Accommodations (Dashboard) ────────────────────────────────────
    Route::prefix('accommodations')->name('accommodations.dashboard.')->group(function () {
        Route::get('/',          [AccommodationController::class, 'index'])->name('index')->middleware('permission:view-accommodations');
        Route::get('{id}',       [AccommodationController::class, 'show'])->name('show')->middleware('permission:view-accommodations');
        Route::post('/',         [AccommodationController::class, 'store'])->name('store')->middleware('permission:create-accommodation');
        Route::post('{id}',      [AccommodationController::class, 'update'])->name('update')->middleware('permission:edit-accommodation');
        Route::delete('{id}',    [AccommodationController::class, 'destroy'])->name('destroy')->middleware('permission:delete-accommodation');
    });

    // ── Journey Requests (Dashboard) ─────────────────────────────────
    Route::prefix('journey-requests')->name('journey-requests.dashboard.')->group(function () {
        Route::get('/',                 [JourneyController::class, 'index'])->name('index')->middleware('permission:view-journey-requests');
        Route::get('{id}',              [JourneyController::class, 'show'])->name('show')->middleware('permission:view-journey-requests');
        Route::patch('{id}/status',     [JourneyController::class, 'updateStatus'])->name('update-status')->middleware('permission:edit-journey-request');
        Route::delete('{id}',           [JourneyController::class, 'destroy'])->name('destroy')->middleware('permission:delete-journey-request');
    });
});
