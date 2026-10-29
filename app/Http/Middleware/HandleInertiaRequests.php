<?php

namespace App\Http\Middleware;

use App\Enums\Permission;
use App\Http\Resources\UserResource;
use App\Services\NavigationService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;
use Spatie\Honeypot\Honeypot;

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

        $settings = app(SettingService::class);
        $address = $request->user()?->defaultDeliveryAddress;
        $user = $request->user();

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user
                    ? UserResource::make($user)
                    : null,
            ],

            'navigation' => fn () => NavigationService::items(),

            'permissions' => Permission::options(),

            'can' => [
                'manageProducts' => Gate::allows('manage-products'),
                'manageOrders' => Gate::allows('manage-orders'),
                'manageAnalitics' => Gate::allows('manage-analitics'),
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
                'manageNomenclature' => Gate::allows('manage-nomenclature'),

                'editAdminNote' => Gate::allows('edit-admin-note'),
                'restore' => Gate::allows('restore'),
                'forceDelete' => Gate::allows('force-delete'),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
                'warning' => fn () => $request->session()->get('warning'),

                'last_uploaded_url' => fn () => $request->session()->get('last_uploaded_url'),
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
                'title' => 'Семейное Ранчо',
                'description' => 'Крымская ферма натуральной молочной продукции.',
                'keywords' => 'домашнее молоко, крым, творог, масло, сыр',
                'robots' => $this->getRobotsForRoute($request),
                'image' => asset('images/og-default.png'),
                'canonical' => url()->current(),
            ],

            'footer' => fn () => $settings->group([
                'site_name',
                'site_description',
                'contact_phone',
                'contact_email',
                'contact_telegram',
                'contact_vk',
                'address_farm',
            ]),

            'promo' => fn () => $request->session()->get('promo'),
            'metrikaId' => fn () => config('services.yandex_metrika.id'),

            'ai' => fn () => [
                'bot_name' => $settings->get('ai_bot_name', 'ИИ-помощник'),
                'greetings' => $settings->get('ai_bot_greetings', 'Могу вам чем-нибудь помочь?'),
                'avatar' => asset('images/ai/bot-avatar.png'),
            ],
            'honeypot' => fn () => new Honeypot(config('honeypot')),
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
