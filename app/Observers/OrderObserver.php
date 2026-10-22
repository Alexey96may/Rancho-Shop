<?php

namespace App\Observers;

use App\Jobs\SendOrderStatusToEmailJob;
use App\Jobs\SendOrderToTelegramJob;
use App\Models\Order;
use App\Services\Admin\AnalyticsService;

class OrderObserver
{
    public function __construct(
        protected AnalyticsService $analyticsService,
    ) {}

    public function created(Order $order): void
    {
        if ($this->isCompleted($order)) {
            $this->analyticsService->updateStats($order);
        }

        SendOrderToTelegramJob::dispatch($order->id)->afterCommit();
    }

    public function updated(Order $order): void
    {
        if ($this->isCompleted($order)) {
            if ($order->isDirty('status') || $order->isDirty('total_price')) {
                $this->analyticsService->updateStats($order);
            }
        } elseif ($order->wasChanged('status') && $order->getOriginal('status') === 'completed') {
            $this->analyticsService->updateStats($order);
        }

        // Уведомление по email при смене статуса
        if ($order->wasChanged('status')) {
            $oldStatus = $order->getOriginal('status');
            $newStatus = $order->status instanceof \UnitEnum
                ? $order->status->value
                : $order->status;

            SendOrderStatusToEmailJob::dispatch($order->id, $oldStatus, $newStatus)
                ->afterCommit();
        }
    }

    public function deleted(Order $order): void
    {
        if ($this->isCompleted($order)) {
            $this->analyticsService->updateStats($order);
        }
    }

    private function isCompleted(Order $order): bool
    {
        $status = $order->status instanceof \UnitEnum ? $order->status->value : $order->status;

        return $status === 'completed';
    }
}
