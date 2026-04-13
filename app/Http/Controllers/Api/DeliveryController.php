<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

            return response()->json(['status' => 'saved']);
        }

        // guest → session
        session(['delivery_draft' => $data]);

        return response()->json(['status' => 'saved']);
    }
}
