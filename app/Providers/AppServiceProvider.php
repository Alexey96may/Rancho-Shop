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
use Illuminate\Support\Str;
use Illuminate\Support\Pluralizer;

use Illuminate\Support\Facades\Gate;
use App\Enums\UserRole;
use App\Enums\Permission;


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

        //Gate::authorize('view-admin-panel');
        Gate::define('view-admin-panel', fn ($user) => $user->isStaff());

        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, function ($user) use ($permission) {

                // ADMIN`s Superpower
                if ($user->role === UserRole::ADMIN) {
                    return true;
                }

                return match ($permission) {
                    // MODERATOR + ADMIN
                    Permission::MANAGE_PRODUCTS,
                    Permission::MANAGE_ANIMALS,
                    Permission::MANAGE_CATEGORIES,
                    Permission::MANAGE_NOMENCLATURE,
                    Permission::MANAGE_CATALOG,
                    Permission::MANAGE_PAGES,
                    Permission::MANAGE_FAQ,
                    Permission::MANAGE_FEATURES,
                    Permission::MANAGE_COMMENTS 
                        => $user->role === UserRole::MODERATOR,

                    // WORKER + ADMIN
                    Permission::MANAGE_DELIVERY,
                    Permission::MANAGE_ANALITICS,
                    Permission::MANAGE_ORDERS 
                        => $user->role === UserRole::WORKER,

                    // ADMIN only
                    Permission::MANAGE_USERS,
                    Permission::MANAGE_SETTINGS,
                    Permission::MANAGE_PROMOCODES 
                        => false,

                    default => false,
                };
            });
        }

        Relation::enforceMorphMap([
            'animal' => Animal::class,
            'product' => Product::class,
            'page' => Page::class,
            'user' => \App\Models\User::class,
            'order' => \App\Models\Order::class,
        ]);

        $this->app->bind(DeliveryDTO::class, function ($app) {

            /** @var Request $request */
            $request = $app->make(Request::class);

            $draft = null;

            // USER
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

            // GUEST
            if (!$draft) {
                $draft = session('delivery_draft');
            }

            // fallback → самовывоз
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

        Str::macro('customSlug', function ($title, $separator = '-', $language = 'en') {
            $dictionary = [
                'шт' => 'pc',
                'кг' => 'kg',
                'гр' => 'g',
                'мл' => 'ml',
                'м'  => 'm',
                'см' => 'cm',
                'мм' => 'mm',
                'л'  => 'l',
            ];

            $title = mb_strtolower($title);

            foreach ($dictionary as $key => $value) {
                $title = preg_replace('/\b' . preg_quote($key, '/') . '\b/u', $value, $title);
            }
            
            return Str::slug($title, $separator, $language);
        });
    }
}
