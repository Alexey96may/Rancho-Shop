<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Http\Requests\CartValidateRequest;

class CartController extends Controller
{
    public function validate(CartValidateRequest $request)
    {
        return response()->json([
            'items' => app(CartService::class)->validate($request->toDTO())
        ]);
    }
}
