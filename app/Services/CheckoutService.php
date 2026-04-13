<?php

namespace App\Services;

use App\DTO\CheckoutDTO;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

use App\Actions\Checkout\GetProductsAction;
use App\Actions\Checkout\ValidateCartAction;
use App\Actions\Checkout\CalculateOrderPriceAction;
use App\Actions\Checkout\CreateOrderAction;
use App\Actions\Checkout\CreateOrderItemsAction;
use App\Actions\Checkout\DecrementStockAction;
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
    ) {}

    public function handle(CheckoutDTO $dto, array $delivery): Order
    {
        return DB::transaction(function () use ($dto, $delivery) {
            $products = $this->getProducts->handle($dto);
            
            Log::info('Checkout started', [
                'items_count' => $dto->items->count(),
            ]);

            $this->validateCart->handle($dto, $products);

            $total = $this->calculatePrice->handle($dto, $products);

            $order = $this->createOrder->handle($dto, $total, $delivery);

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