<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'address' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'is_valid' => 'required|boolean',
        ]);

        $user = $request->user();

        if ($user) {
            $user->update([
                'last_delivery_address' => $data['address'],
                'last_delivery_lat' => $data['lat'],
                'last_delivery_lng' => $data['lng'],
            ]);
        } else {
            session([
                'delivery_draft' => $data,
            ]);
        }

        return back()->with('success', 'Ваше местоположение сохранено!'); // 👈 ВАЖНО: Inertia refresh
    }
}
