<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService
    ) {}

    public function __invoke(CheckoutRequest $request): JsonResponse|OrderResource
    {
            $order = $this->checkoutService->handle(
                $request->toDTO()
            );

            return new OrderResource($order);
    }
}
