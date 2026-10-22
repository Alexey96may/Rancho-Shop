<?php

namespace App\Jobs;

use App\Mail\AdminNewOrderMail;
use App\Mail\CustomerNewOrderMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendNewOrderToEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public readonly int $orderId,
    ) {}

    public function handle(): void
    {
        $order = Order::with(['items', 'user', 'promoCode'])->find($this->orderId);

        if (!$order) {
            Log::warning('SendNewOrderToEmailJob: order not found', ['id' => $this->orderId]);

            return;
        }

        // 1. Админу
        $adminEmail = config('app.admin_email');

        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new AdminNewOrderMail($order));
            } catch (Throwable $e) {
                Log::error('SendNewOrderToEmailJob: failed to send admin email', [
                    'order_id' => $this->orderId,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        // 2. Клиенту
        $customerEmail = $order->user?->email ?? $order->customer_email ?? null;

        if ($customerEmail) {
            try {
                Mail::to($customerEmail)->send(new CustomerNewOrderMail($order));
            } catch (Throwable $e) {
                Log::error('SendNewOrderToEmailJob: failed to send customer email', [
                    'order_id' => $this->orderId,
                    'email' => $customerEmail,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
