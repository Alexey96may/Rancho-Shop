<?php

use App\Http\Controllers\Api\ApiLandingController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;

// /api/landing
Route::get('/landing', [ApiLandingController::class, 'index']);

Route::prefix('auth/google')->group(function () {
    Route::get('/url', [SocialAuthController::class, 'getGoogleUrl']);
    
    Route::get('/callback', [SocialAuthController::class, 'handleGoogleCallback']);
});

Route::prefix('v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product:slug}', [ProductController::class, 'show']);
});


Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Route::get('/admin/stats', [AdminController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:worker'])->group(function () {
    // Route::get('/tasks', [TaskController::class, 'index']);
});