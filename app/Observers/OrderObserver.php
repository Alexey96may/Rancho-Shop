<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\Admin\AnalyticsService;

class OrderObserver
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if ($this->isCompleted($order)) {
            $this->analyticsService->updateStats($order);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($this->isCompleted($order)) {
            if ($order->isDirty('status') || $order->isDirty('total_price')) {
                $this->analyticsService->updateStats($order);
            }
        }

        elseif ($order->wasChanged('status') && $order->getOriginal('status') === 'completed') {
            $this->analyticsService->updateStats($order);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        if ($this->isCompleted($order)) {
            $this->analyticsService->updateStats($order);
        }
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }

    /**
    * Helper for checking status (supports Enums)
    */
    private function isCompleted(Order $order): bool
    {
        $status = $order->status instanceof \UnitEnum ? $order->status->value : $order->status;
        return $status === 'completed';
    }
}
