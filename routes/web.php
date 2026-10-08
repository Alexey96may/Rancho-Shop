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
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Profile\ProfileCommentController;
use App\Http\Controllers\Profile\ProfileOrderController;

use App\Enums\Permission;

Route::get('/', [LandingController::class, 'index'])->name('home');

// Route::get('/auth/google', [SocialController::class, 'redirectToGoogle'])->name('auth.google');
// Route::get('/auth/google/callback', [SocialController::class, 'handleGoogleCallback']);

Route::get('/catalog', [ProductController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{product:slug}', [ProductController::class, 'show'])->name('catalog.show');

Route::get('/cart', function () {
    return inertia('Cart/Index');
})->name('cart.index');

Route::get('/checkout', [CheckoutPageController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutPageController::class, 'store'])->name('checkout.store');

Route::get('/checkout/success/{order}', [CheckoutPageController::class, 'success'])->name('checkout.success');

Route::get('/delivery', [PageController::class, 'delivery'])->name('delivery');
Route::get('/about', [PageController::class, 'about'])->name('about');

Route::get('/animals', [AnimalController::class, 'index'])->name('animals.index');
Route::get('/animals/{animal:slug}', [AnimalController::class, 'show'])->name('animals.show');

Route::get('/comments', [CommentController::class, 'index'])->name('reviews.index');
Route::post('/comments', [CommentController::class, 'store'])->middleware('auth')->name('comments.store');

Route::post('/delivery/draft', [DeliveryController::class, 'store'])->name('delivery.draft.store');

Route::middleware('auth')->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'edit'])->name('edit');
    Route::patch('/', [ProfileController::class, 'update'])->name('update');
    Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');

    Route::get('/comments', [ProfileCommentController::class, 'index'])->name('comments.index');
    Route::post('/comments', [ProfileCommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{comment}', [ProfileCommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [ProfileCommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('/orders', [ProfileOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{order}/cancel', [ProfileOrderController::class, 'cancel'])->name('orders.cancel');
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
            Route::patch('products/{product}/restore', [AdminProductController::class, 'restore'])->name('products.restore')->withTrashed();
            Route::resource('products', AdminProductController::class)->withTrashed();

            Route::resource('categories', CategoryController::class);

            Route::resource('catalog', CatalogController::class);
            Route::patch('catalog/{variant}/quick', [CatalogController::class, 'quickUpdate'])->name('catalog.quick');

            Route::delete('animals/{animal}/media/{media}', [AdminAnimalController::class, 'deleteMedia'])
                ->name('animals.media.destroy');
            Route::patch('animals/{animal}/restore', [AdminAnimalController::class, 'restore'])
                ->name('animals.restore')->withTrashed();
            Route::resource('animals', AdminAnimalController::class);

            Route::resource('pages', AdminPageController::class);
            Route::post('pages/upload-media/{page}', [AdminPageController::class, 'uploadMedia'])->name('pages.upload-media');
            Route::post('media/upload-temporary', [AdminPageController::class, 'uploadTemporaryMedia'])->name('media.upload-temporary');

            Route::prefix('faq')->name('faq.')->group(function () {
                Route::patch('reorder', [FaqController::class, 'reorder'])->name('reorder');

                Route::patch('{faq}/toggle', [FaqController::class, 'toggle'])->name('toggle');
            });
            Route::resource('faq', FaqController::class);

            Route::resource('features', FeatureController::class);
            Route::patch('features/{feature}/toggle', [FeatureController::class, 'toggle'])
                ->name('features.toggle');

            Route::patch('comments/{comment}/restore', [AdminCommentController::class, 'restore'])->name('comments.restore')->withTrashed();
            Route::resource('comments', AdminCommentController::class)->only(['index', 'update', 'destroy'])->withTrashed();
        });

        // (Admin, Worker)
        Route::middleware('can:' . Permission::MANAGE_ORDERS->value)->group(function () {
            Route::resource('orders', AdminOrderController::class);

            Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

            Route::patch('units/reorder', [UnitController::class, 'reorder'])->name('units.reorder');
            Route::resource('units', UnitController::class)->except(['show', 'create']);
        });

        // (Admin)
        Route::middleware('can:' . Permission::MANAGE_USERS->value)->group(function () {
            Route::resource('users', UserController::class);

            Route::patch('promocodes/{promoCode}/toggle', [PromocodeController::class, 'toggle'])
                ->name('promocodes.toggle');
            Route::resource('promocodes', PromocodeController::class);

            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [SettingController::class, 'index'])->name('index');
                Route::post('/bulk', [SettingController::class, 'bulkUpdate'])->name('bulk');
                Route::post('/clear-cache', [SettingController::class, 'clearCache'])->name('clear-cache');
            });
        });
});

Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show');
