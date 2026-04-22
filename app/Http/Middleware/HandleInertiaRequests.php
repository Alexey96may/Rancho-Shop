<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Http\Resources\UserResource;
use App\Enums\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $address = $request->user()?->defaultDeliveryAddress;


        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? UserResource::make($request->user())
                    : null,
            ],

            'permissions' => Permission::options(),

            'can' => [
                'manageProducts' => Gate::allows('manage-products'),
                'manageOrders' => Gate::allows('manage-orders'),
                'manageComments' => Gate::allows('manage-comments'),
                'manageDelivery' => Gate::allows('manage-delivery'),
                'manageAnimals' => Gate::allows('manage-animals'),
                'manageUsers' => Gate::allows('manage-users'),
                'manageCategories' => Gate::allows('manage-categories'),
                'manageCatalog' => Gate::allows('manage-catalog'),
                'managePages' => Gate::allows('manage-pages'),
                'managePromocodes' => Gate::allows('manage-promocodes'),
                'manageFaq' => Gate::allows('manage-faq'),
                'manageFeatures' => Gate::allows('manage-features'),
                'manageSettings' => Gate::allows('manage-settings'),
            ],

            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn() => $request->session()->get('error'),
                'message' => fn() => $request->session()->get('message'),
                'warning' => fn() => $request->session()->get('warning'),
            ],

            'deliveryDraft' => $request->user()
                ? [
                    'address' => $address?->address,
                    'lat' => $address?->lat,
                    'lng' => $address?->lng,
                    'is_valid' => (bool) $address,
                ]
                : session('delivery_draft'),

            'seo' => [
                'title'       => 'Молочная Долина',
                'description' => 'Крымский магазин натуральной молочной продукции.',
                'keywords'    => 'домашнее молоко, крым, творог, масло, сыр',
                'robots'      => $this->getRobotsForRoute($request),
                'image'       => asset('images/og-default.png'),
                'canonical'   => url()->current(),
            ],
        ];
    }

    /**
    * Automatically close the admin panel from indexing
    */
    private function getRobotsForRoute(Request $request): string
    {
        return $request->is('admin*') ? 'noindex, nofollow' : 'index, follow';
    }
}
