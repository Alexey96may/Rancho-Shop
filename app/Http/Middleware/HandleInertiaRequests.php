<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Http\Resources\UserResource;

use Illuminate\Support\Facades\Gate;

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

            'can' => [
                'adminPanel' => $request->user()
                    ? Gate::allows('view-admin-panel')
                    : false,

                'manageUsers' => $request->user()
                    ? Gate::allows('manage-users')
                    : false,
            ],

            'deliveryDraft' => $request->user()
                ? [
                    'address' => $address?->address,
                    'lat' => $address?->lat,
                    'lng' => $address?->lng,
                    'is_valid' => (bool) $address,
                ]
                : session('delivery_draft'),
        ];
    }
}
