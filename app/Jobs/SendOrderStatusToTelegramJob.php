<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Telegram\OrderTelegramNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderStatusToTelegramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $orderId,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {}

    public function handle(OrderTelegramNotifier $notifier): void
    {
        $order = Order::with(['items', 'user', 'promoCode'])->find($this->orderId);

        if (!$order) {
            return;
        }

        $notifier->notifyStatusChanged($order, $this->oldStatus, $this->newStatus);
    }
}
