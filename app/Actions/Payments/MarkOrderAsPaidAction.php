<?php

namespace App\Actions\Payments;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class MarkOrderAsPaidAction
{
    public function handle(Order $order, ?string $paymentId): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => OrderStatus::CONFIRMED,
            'payment_id' => $paymentId,
        ]);

        Log::info('Order marked as paid', [
            'order_id' => $order->id,
            'payment_id' => $paymentId,
        ]);
    }
}
