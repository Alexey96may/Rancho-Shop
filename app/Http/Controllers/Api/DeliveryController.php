<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryAddressResource;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * GET saved delivery addresses (user only)
     */
    public function index(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'data' => [],
                'guest' => true,
            ]);
        }

        return DeliveryAddressResource::collection(
            $request->user()->deliveryAddresses
        );
    }

    /**
     * SAVE delivery draft (user or guest)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'address' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'is_valid' => 'required|boolean',
        ]);

        $user = $request->user();
        $sessionId = session()->getId();

        // 👤 AUTH USER
        if ($user) {
            $address = $user->deliveryAddresses()->create([
                'address' => $data['address'],
                'lat' => $data['lat'],
                'lng' => $data['lng'],
            ]);

            return response()->json([
                'status' => 'saved',
                'data' => new DeliveryAddressResource($address),
                'type' => 'user',
            ]);
        }

        // 👤 GUEST (session storage)
        session([
            'delivery_draft' => [
                'address' => $data['address'],
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'is_valid' => $data['is_valid'],
                'session_id' => $sessionId,
            ],
        ]);

        return response()->json([
            'status' => 'saved',
            'type' => 'guest',
        ]);
    }

    /**
     * GET guest draft
     */
    public function draft(Request $request)
    {
        return response()->json([
            'data' => session('delivery_draft'),
        ]);
    }

    /**
     * CLEAR draft (after order)
     */
    public function clearDraft(Request $request)
    {
        session()->forget('delivery_draft');

        return response()->json([
            'status' => 'cleared',
        ]);
    }
}
