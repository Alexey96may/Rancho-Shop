<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartValidateRequest;
use App\Services\CartService;

class CartController extends Controller
{
    public function validateCart(CartValidateRequest $request)
    {
        return response()->json([
            'items' => app(CartService::class)->validate($request->toDTO()),
        ]);
    }
}
