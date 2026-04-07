<?php

use App\Http\Controllers\Api\ApiLandingController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;

// /api/landing
Route::get('/landing', [ApiLandingController::class, 'index']);

Route::prefix('auth/google')->group(function () {
    Route::get('/url', [SocialAuthController::class, 'getGoogleUrl']);
    
    Route::get('/callback', [SocialAuthController::class, 'handleGoogleCallback']);
});