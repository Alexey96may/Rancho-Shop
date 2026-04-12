<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Services\SettingService;

class DeliveryController extends Controller
{
    public function index(SettingService $settings)
    {
        return Inertia::render('Pages/Delivery/Index', [
            'settings' => $settings->all(),

            'delivery' => [
                'farm_coords' => $settings->get('farm_coords'),
                'delivery_cost' => $settings->get('delivery_cost'),
                'free_delivery_from' => $settings->get('free_delivery_from'),
                'address_farm' => $settings->get('address_farm'),
            ],
        ]);
    }
}
