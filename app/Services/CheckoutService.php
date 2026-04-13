<?php

namespace App\Services;

use App\DTO\CheckoutDTO;
use App\DTO\DeliveryDTO;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

use App\Actions\Checkout\GetProductsAction;
use App\Actions\Checkout\ValidateCartAction;
use App\Actions\Checkout\CalculateOrderPriceAction;
use App\Actions\Checkout\CreateOrderAction;
use App\Actions\Checkout\CreateOrderItemsAction;
use App\Actions\Checkout\DecrementStockAction;
use App\Actions\Checkout\ValidateDeliveryAction;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    public function __construct(
        protected GetProductsAction $getProducts,
        protected ValidateCartAction $validateCart,
        protected CalculateOrderPriceAction $calculatePrice,
        protected CreateOrderAction $createOrder,
        protected CreateOrderItemsAction $createItems,
        protected DecrementStockAction $decrementStock,
        protected ValidateDeliveryAction $validateDelivery,
    ) {}

    public function handle(CheckoutDTO $dto, DeliveryDTO $delivery): Order
    {
        return DB::transaction(function () use ($dto, $delivery) {
            $products = $this->getProducts->handle($dto);
            
            Log::info('Checkout started', [
                'items_count' => $dto->items->count(),
            ]);

            $this->validateCart->handle($dto, $products);

            $deliveryResult = $this->validateDelivery->handle($delivery);

            $total = $this->calculatePrice->handle($dto, $products);

            $order = $this->createOrder->handle($dto, $total, $delivery, $deliveryResult);

            $this->createItems->handle($order, $dto, $products);

            $this->decrementStock->handle($dto, $products);

            Log::info('Order created', [
                'order_id' => $order->id,
                'total' => $order->total_price,
            ]);

            return $order->load('items');
        });
    }
}