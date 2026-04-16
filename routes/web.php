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
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DeliveryController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\DeliveryController as AdminDeliveryController;
use App\Http\Controllers\Admin\AnimalController as AdminAnimalController;
use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PromocodeController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\SettingController;


Route::get('/', [LandingController::class, 'index'])->name('home');

// Route::get('/auth/google', [SocialController::class, 'redirectToGoogle'])->name('auth.google');
// Route::get('/auth/google/callback', [SocialController::class, 'handleGoogleCallback']);

Route::get('/catalog', [ProductController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{product:slug}', [ProductController::class, 'show'])->name('catalog.show');

Route::get('/cart', function () {
    return inertia('Cart/Index');
})->name('cart.index');

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

Route::get('/checkout', [CheckoutPageController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutPageController::class, 'store'])->name('checkout.store');
    
Route::get('/delivery', [PageController::class, 'delivery'])->name('delivery');

Route::get('/animals', [AnimalController::class, 'index'])->name('animals.index');
Route::get('/animals/{animal:slug}', [AnimalController::class, 'show'])->name('animals.show');

Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');

Route::get('/about', [PageController::class, 'about'])->name('about');

Route::post('/delivery/draft', [DeliveryController::class, 'store'])->name('delivery.draft.store');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'can:view-admin-panel'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

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

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin,moderator,worker'])
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Products
        Route::resource('products', AdminProductController::class);

        // Orders
        Route::resource('orders', AdminOrderController::class);

        // Users
        Route::resource('users', UserController::class);

        // Categories
        Route::resource('categories', CategoryController::class);

        // Comments
        Route::resource('comments', AdminCommentController::class);

        // Delivery
        Route::resource('delivery', AdminDeliveryController::class);

        // Animals
        Route::resource('animals', AdminAnimalController::class);

        // Catalog (SKU / variants)
        Route::resource('catalog', CatalogController::class);

        // Pages (CMS)
        Route::resource('pages', AdminPageController::class);

        // Promocodes
        Route::resource('promocodes', PromocodeController::class);

        // FAQ
        Route::resource('faq', FaqController::class);

        // Features
        Route::resource('features', FeatureController::class);

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    });