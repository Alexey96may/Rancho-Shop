<?php

namespace App\Jobs;

use App\Mail\AdminOrderStatusMail;
use App\Mail\CustomerOrderStatusMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderStatusToEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public readonly int $orderId,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {}

    public function handle(): void
    {
        $order = Order::with(['items', 'user', 'promoCode'])->find($this->orderId);

        if (!$order) {
            Log::warning('SendOrderStatusToEmailJob: order not found', ['id' => $this->orderId]);

            return;
        }

        // 1. Админу — всегда
        $adminEmail = config('app.admin_email');

        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(
                    new AdminOrderStatusMail($order, $this->oldStatus, $this->newStatus)
                );
            } catch (Throwable $e) {
                Log::error('SendOrderStatusToEmailJob: failed to send admin email', [
                    'order_id' => $this->orderId,
                    'message' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('SendOrderStatusToEmailJob: admin email is not configured');
        }

        // 2. Клиенту — если есть email
        $customerEmail = $order->user?->email ?? $order->customer_email ?? null;

        if ($customerEmail) {
            try {
                Mail::to($customerEmail)->send(
                    new CustomerOrderStatusMail($order, $this->newStatus)
                );
            } catch (Throwable $e) {
                Log::error('SendOrderStatusToEmailJob: failed to send customer email', [
                    'order_id' => $this->orderId,
                    'email' => $customerEmail,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
