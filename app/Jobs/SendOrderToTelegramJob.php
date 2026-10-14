<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Telegram\OrderTelegramNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOrderToTelegramJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public readonly int $orderId,
    ) {}

    public function handle(OrderTelegramNotifier $notifier): void
    {
        $order = Order::find($this->orderId);
        if (!$order) {
            return;
        }

        $notifier->notifyNewOrder($order);
    }
}
