<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    //login
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    // Registration
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth:api');
    // Refresh Token
    Route::post('refresh-token', [AuthController::class, 'refresh'])->name('auth.refresh')->middleware('auth:api');
});
