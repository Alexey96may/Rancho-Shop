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

    public function handle(CheckoutDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {

            $products = $this->getProducts->handle($dto);

            $this->validateCart->handle($dto, $products);

            $total = $this->calculatePrice->handle($dto, $products);

            $order = $this->createOrder->handle($dto, $total);

            $this->createItems->handle($order, $dto, $products);

            $this->decrementStock->handle($dto, $products);

            return $order->load('items');
        });
    }
}