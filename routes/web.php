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
use App\Http\Controllers\Admin\AnimalController as AdminAnimalController;
use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PromocodeController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\DashboardController as UserDashboardController;
use App\Enums\Permission;

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

Route::middleware(['auth', 'verified'])->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

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

        // (Admin, Moderator)
        Route::middleware('can:' . Permission::MANAGE_PRODUCTS->value)->group(function () {
            Route::resource('products', AdminProductController::class);
            Route::resource('categories', CategoryController::class);

            Route::resource('catalog', CatalogController::class);
            Route::patch('catalog/{id}/quick', [CatalogController::class, 'quickUpdate'])->name('catalog.quick');

            Route::resource('animals', AdminAnimalController::class);
            Route::delete('animals/{animal}/media/{media}', [AnimalController::class, 'deleteMedia'])
                ->name('animals.media.destroy');

            Route::resource('pages', AdminPageController::class);
            Route::resource('faq', FaqController::class);

            Route::resource('features', FeatureController::class);
            Route::patch('features/{feature}/toggle', [FeatureController::class, 'toggle'])
                ->name('features.toggle');

            Route::resource('comments', AdminCommentController::class);
        });

        // (Admin, Worker)
        Route::middleware('can:' . Permission::MANAGE_ORDERS->value)->group(function () {
            Route::resource('orders', AdminOrderController::class);
            Route::resource('units', UnitController::class)->except(['show', 'create']);
            Route::patch('units/reorder', [UnitController::class, 'reorder'])->name('units.reorder');
        });

        // (Admin)
        Route::middleware('can:' . Permission::MANAGE_USERS->value)->group(function () {
            Route::resource('users', UserController::class);
            Route::resource('promocodes', PromocodeController::class);
            
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [SettingController::class, 'index'])->name('index');
                Route::post('/bulk', [SettingController::class, 'bulkUpdate'])->name('bulk');
                Route::post('/clear-cache', [SettingController::class, 'clearCache'])->name('clear-cache');
            });
        });
});

Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show');