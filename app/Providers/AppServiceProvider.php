<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\DTO\DeliveryDTO;
use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\Animal;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\Seo;
use App\Models\User;
use App\Observers\SeoCleanupObserver;
use App\Observers\SeoObserver;
use App\Observers\SitemapCacheObserver;
use App\Services\Payments\DirectPaymentGateway;
use App\Services\Payments\FakePaymentGateway;
use App\Services\Payments\PayMasterGateway;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Автоматический выбор платежного драйвера из .env
        $this->app->bind(PaymentGatewayInterface::class, function () {
            $driver = config('services.payment.driver', 'fake');

            if (app()->isProduction() && config('services.payment.driver') === 'fake') {
                throw new \RuntimeException('PAYMENT_DRIVER=fake недопустим в production');
            }

            return match ($driver) {
                'paymaster' => new PayMasterGateway,
                'direct' => new DirectPaymentGateway,
                default => new FakePaymentGateway,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Gate::authorize('view-admin-panel');
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
                    Permission::MANAGE_COMMENTS => $user->role === UserRole::MODERATOR,

                    // WORKER + ADMIN
                    Permission::MANAGE_DELIVERY,
                    Permission::MANAGE_ANALITICS,
                    Permission::MANAGE_ORDERS => $user->role === UserRole::WORKER,

                    // ADMIN only
                    Permission::MANAGE_USERS,
                    Permission::MANAGE_SETTINGS,
                    Permission::MANAGE_PROMOCODES => false,

                    default => false,
                };
            });
        }

        Gate::define('edit-admin-note', fn ($user) => $user->role === UserRole::ADMIN);
        Gate::define('restore', fn ($user) => $user->role === UserRole::ADMIN);
        Gate::define('force-delete', fn ($user) => $user->role === UserRole::ADMIN);

        $models = [
            Product::class,
            Page::class,
            Animal::class,
        ];

        foreach ($models as $model) {
            $model::observe(SeoCleanupObserver::class);
            $model::observe(SitemapCacheObserver::class);
        }

        Seo::observe(SeoObserver::class);

        Relation::enforceMorphMap([
            'animal' => Animal::class,
            'product' => Product::class,
            'page' => Page::class,
            'user' => User::class,
            'order' => Order::class,
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
                'м' => 'm',
                'см' => 'cm',
                'мм' => 'mm',
                'л' => 'l',
            ];

            $title = mb_strtolower($title);

            foreach ($dictionary as $key => $value) {
                $title = preg_replace('/\b' . preg_quote($key, '/') . '\b/u', $value, $title);
            }

            return Str::slug($title, $separator, $language);
        });
    }
}
