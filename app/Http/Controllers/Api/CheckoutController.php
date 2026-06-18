<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

use Throwable;

use App\Exceptions\Checkout\CheckoutException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Checkout API is alive',
        ]);
    }

    public function store(CheckoutRequest $request)
    {
        $order = $this->checkoutService->handle(
            $request->toDTO()
        );

        return response()->json([
            'message' => 'Order created successfully',
            'data' => new OrderResource($order),
        ], 201);
    }
}
