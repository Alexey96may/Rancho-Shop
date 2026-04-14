<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

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
        $deliveryDraft = $request->user()
                ? [
                    'address' => $request->user()->last_delivery_address,
                    'lat' => $request->user()->last_delivery_lat,
                    'lng' => $request->user()->last_delivery_lng,
                    'is_valid' => true,
                ]
                : session('delivery_draft');


        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],

            'deliveryDraft' => $request->user()
                ? [
                    'address' => $request->user()->last_delivery_address,
                    'lat' => $request->user()->last_delivery_lat,
                    'lng' => $request->user()->last_delivery_lng,
                    'is_valid' => true,
                ]
                : session('delivery_draft'),
        ];
    }
}
