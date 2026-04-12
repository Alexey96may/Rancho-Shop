<?php

use App\Http\Controllers\Api\ApiLandingController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
// use App\Http\Controllers\Api\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\AnimalApiController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PageController;

// /api/landing
Route::get('/landing', [ApiLandingController::class, 'index']);

Route::prefix('auth/google')->group(function () {
    // Route::get('/url', [SocialAuthController::class, 'getGoogleUrl']);
    
    // Route::get('/callback', [SocialAuthController::class, 'handleGoogleCallback']);
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product:slug}', [ProductController::class, 'show']);

Route::post('/cart/validate', [CartController::class, 'validate']);
Route::get('/checkout', [CheckoutController::class, 'index']);
Route::post('/checkout', [CheckoutController::class, 'store']);

Route::get('/animals', [AnimalApiController::class, 'index']);
Route::get('/animals/{animal:slug}', [AnimalApiController::class, 'show']);

Route::get('/animals/{animal}/comments', [CommentController::class, 'index']);
Route::get('/comments', [CommentController::class, 'index']);
Route::post('/comments', [CommentController::class, 'store']);
Route::get('/pages/{slug}', [PageController::class, 'show']);


Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Route::get('/admin/stats', [AdminController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:worker'])->group(function () {
    // Route::get('/tasks', [TaskController::class, 'index']);
});