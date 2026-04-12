<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
// use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CheckoutPageController;
use App\Http\Controllers\AnimalController;

Route::get('/', [LandingController::class, 'index'])->name('home');

// Route::get('/auth/google', [SocialController::class, 'redirectToGoogle'])->name('auth.google');
// Route::get('/auth/google/callback', [SocialController::class, 'handleGoogleCallback']);

Route::get('/catalog', [ProductController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{product:slug}', [ProductController::class, 'show'])->name('catalog.show');

Route::get('/cart', function () {
        return inertia('Cart/Index');
    })->name('cart.index');

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

Route::get('/checkout', [CheckoutPageController::class, 'index'])
    ->name('checkout.index');

Route::post('/checkout', [CheckoutPageController::class, 'store'])
    ->name('checkout.store');

Route::get('/animals', [AnimalController::class, 'index'])->name('animals.index');
Route::get('/animals/{animal:slug}', [AnimalController::class, 'show'])->name('animals.show');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    // Route::get('/admin/dashboard', function () {
    //     return Inertia::render('Admin/Dashboard', [
    //         'stats' => [/* твои данные */]
    //     ]);
    // });
});

Route::middleware(['auth', 'role:worker'])->group(function () {
    // Route::get('/worker/tasks', function () {
    //     return Inertia::render('Worker/TaskList');
    // });
});

require __DIR__ . '/auth.php';
