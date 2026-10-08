<?php

namespace App\Services;

use App\DTO\CheckoutDTO;
use App\DTO\DeliveryDTO;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use App\Actions\Checkout\GetProductsAction;
use App\Actions\Checkout\ValidateCartAction;
use App\Actions\Checkout\CalculateOrderPriceAction;
use App\Actions\Checkout\CreateOrderAction;
use App\Actions\Checkout\CreateOrderItemsAction;
use App\Actions\Checkout\DecrementStockAction;
use App\Actions\Checkout\ResolveCheckoutUserAction;
use App\Actions\Checkout\ValidateDeliveryAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

    public function handle(CheckoutDTO $dto, DeliveryDTO $delivery): Order
    {

        /** @var array{user: ?User, justCreated: bool}|null $result */
        $result = null;

        $order = DB::transaction(function () use ($dto, $delivery, &$result) {
            $products = $this->getProducts->handle($dto);
            $variants = $products->pluck('variants')->flatten()->keyBy('id');

            Log::info('Checkout started', [
                'items_count' => $dto->items->count(),
            ]);

            $this->validateCart->handle($dto, $variants);

            $deliveryResult = $this->validateDelivery->handle($delivery);

            $result = $this->resolveUser->handle($dto);
            $user = $result['user'];

            $total = $this->calculatePrice->handle($dto, $variants);

            $order = $this->createOrder->handle($dto, $total, $delivery, $deliveryResult, $user);

            $this->createItems->handle($order, $dto, $products);

            $this->decrementStock->handle($dto, $variants);

            Log::info('Order created', [
                'order_id' => $order->id,
                'total' => $order->total_price,
            ]);

            return $order->load('items');
        });

        if ($result['justCreated'] ?? false) {
            Auth::login($result['user']);
        }

        return $order;
    }
}
