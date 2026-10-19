<?php

namespace App\Services;

use App\Actions\Checkout\CalculateOrderPriceAction;
use App\Actions\Checkout\CreateOrderAction;
use App\Actions\Checkout\CreateOrderItemsAction;
use App\Actions\Checkout\DecrementStockAction;
use App\Actions\Checkout\GetProductsAction;
use App\Actions\Checkout\ResolveCheckoutUserAction;
use App\Actions\Checkout\ValidateCartAction;
use App\Actions\Checkout\ValidateDeliveryAction;
use App\DTO\CheckoutDTO;
use App\DTO\DeliveryDTO;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        protected GetProductsAction $getProducts,
        protected ValidateCartAction $validateCart,
        protected CalculateOrderPriceAction $calculatePrice,
        protected ResolveCheckoutUserAction $resolveUser,
        protected CreateOrderAction $createOrder,
        protected CreateOrderItemsAction $createItems,
        protected DecrementStockAction $decrementStock,
        protected ValidateDeliveryAction $validateDelivery,
    ) {}

    public function handle(CheckoutDTO $dto, DeliveryDTO $delivery): array
    {

        /** @var array{user: ?User, justCreated: bool}|null $result */
        $result = null;

        $order = DB::transaction(function () use ($dto, $delivery, &$result) {
            $products = $this->getProducts->handle($dto);
            $variants = $products->pluck('variants')->flatten()->keyBy('id');

            $this->validateCart->handle($dto, $variants);

            $deliveryResult = $this->validateDelivery->handle($delivery);

            $result = $this->resolveUser->handle($dto);
            $user = $result['user'];

            $total = $this->calculatePrice->handle($dto, $variants);

            $order = $this->createOrder->handle($dto, $total, $delivery, $deliveryResult, $user);

            $this->createItems->handle($order, $dto, $products);

            $this->decrementStock->handle($dto, $variants);

            return $order->load('items');
        });

        return [
            'order' => $order,
            'magicLink' => $result['magicLink'] ?? null,
        ];
    }
}
