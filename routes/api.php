<?php

use App\Http\Controllers\Api\ApiLandingController;
use Illuminate\Support\Facades\Route;

// /api/landing
Route::get('/landing', [ApiLandingController::class, 'index']);