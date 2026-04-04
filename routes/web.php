<?php

use App\Http\Controllers\ProfileController;
use App\Models\Animal;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $mediaMapper = fn ($m) => [
        'id' => $m->id,
        'url' => $m->getUrl(),
        'thumbnail' => $m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : null,
        'preview' => $m->hasGeneratedConversion('preview') ? $m->getUrl('preview') : null,
        'name' => $m->name,
        'file_name' => $m->file_name,
        'mime_type' => $m->mime_type,
        'size' => $m->size,
        'order_column' => $m->order_column,
    ];
    
    return Inertia::render('HomeView', [
        'products' => Product::with('media')->get()->map(function ($product) use ($mediaMapper) {
            return [
                ...$product->toArray(),
                'media' => $product->media->map($mediaMapper),
            ];
        }),
        'cows' => Animal::with('media')->cows()->get()->map(function ($animal) use ($mediaMapper) {
            $voiceMedia = $animal->media->first(fn($m) => str_contains($m->mime_type, 'audio'));

            return [
                ...$animal->toArray(),
                'media' => $animal->media->map($mediaMapper),
                'voice_url' => $voiceMedia ? $voiceMedia->getUrl() : null,
            ];
        }),
    ]);
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
