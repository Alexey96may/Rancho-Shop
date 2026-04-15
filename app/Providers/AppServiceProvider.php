<?php

namespace App\Providers;

use App\Models\Animal;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use App\DTO\DeliveryDTO;

use Illuminate\Support\Facades\Gate;
use App\Enums\UserRole;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        //Gate::authorize('admin-access');
        Gate::define('admin-access', function ($user) {
            return $user->role === UserRole::ADMIN;
        });

        Relation::enforceMorphMap([
            'animal' => Animal::class,
            'product' => Product::class,
            'page' => Page::class,
        ]);

        $this->app->bind(DeliveryDTO::class, function ($app) {

            /** @var Request $request */
            $request = $app->make(Request::class);

            $draft = null;

            // 👤 USER
            if ($request->user()) {
                $address = $request->user()
                    ->deliveryAddresses()
                    ->latest()
                    ->first();

                if ($address) {
                    $draft = [
                        'address' => $address->address,
                        'lat' => $address->lat,
                        'lng' => $address->lng,
                        'is_valid' => true,
                        'is_pickup' => false,
                    ];
                }
            }

            // 👤 GUEST
            if (!$draft) {
                $draft = session('delivery_draft');
            }

            // ❗ fallback → самовывоз
            if (!$draft) {
                return new DeliveryDTO(
                    address: null,
                    lat: null,
                    lng: null,
                    is_pickup: true,
                    is_valid: true,
                    meta: null,
                );
            }

            return new DeliveryDTO(
                address: $draft['address'] ?? null,
                lat: $draft['lat'] ?? null,
                lng: $draft['lng'] ?? null,
                is_pickup: $draft['is_pickup'] ?? false,
                is_valid: $draft['is_valid'] ?? false,
                meta: $draft['delivery_meta'] ?? null,
            );
        });
    }
}
