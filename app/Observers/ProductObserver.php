<?php

namespace App\Observers;

use App\Jobs\SendProductAvailableToSubscribersJob;
use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Реагируем только если изменилось что-то, что влияет на доступность:
        // is_active, или сток дефолтного варианта
        $isActiveChanged = $product->wasChanged('is_active');

        if (!$isActiveChanged) {
            return;
        }

        if (!$product->is_active) {
            return;
        }

        // Товар стал активен — проверяем, есть ли сток у дефолтного варианта
        $default = $product->defaultVariant()->first();

        if (!$default || (float) $default->stock <= 0) {
            return;
        }

        SendProductAvailableToSubscribersJob::dispatch($product->id)->afterCommit();
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
