<?php

namespace App\Observers;

use App\Jobs\SendProductAvailableToSubscribersJob;
use App\Models\ProductVariant;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "created" event.
     */
    public function created(ProductVariant $productVariant): void
    {
        //
    }

    /**
     * Handle the ProductVariant "updated" event.
     */
    public function updated(ProductVariant $variant): void
    {
        if (!$variant->wasChanged('stock')) {
            return;
        }

        $oldStock = (float) $variant->getOriginal('stock');
        $newStock = (float) $variant->stock;

        // Интересует только переход «было 0 → стало > 0»
        if ($oldStock > 0 || $newStock <= 0) {
            return;
        }

        $productId = $variant->product_id;

        if (!$productId) {
            return;
        }

        SendProductAvailableToSubscribersJob::dispatch($productId)->afterCommit();
    }

    /**
     * Handle the ProductVariant "deleted" event.
     */
    public function deleted(ProductVariant $catalog): void
    {
        if (!$catalog->product) {
            return;
        }

        if ($catalog->is_default) {
            $newDefault = $catalog->product->variants()
                ->orderByDesc('stock')
                ->first();

            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }
    }

    /**
     * Handle the ProductVariant "restored" event.
     */
    public function restored(ProductVariant $productVariant): void
    {
        //
    }

    /**
     * Handle the ProductVariant "force deleted" event.
     */
    public function forceDeleted(ProductVariant $productVariant): void
    {
        //
    }

    /**
     * Triggers upon creation or update, prior to writing to the database.
     */
    public function saving(ProductVariant $catalog): void
    {
        if (!$catalog->product) {
            return;
        }

        if ($catalog->product->variants()->count() === 0) {
            $catalog->is_default = true;

            return;
        }

        if ($catalog->isDirty('is_default') && $catalog->is_default === false && $catalog->getOriginal('is_default') === true) {
            $newDefault = $catalog->product->variants()
                ->where('id', '!=', $catalog->id)
                ->orderByDesc('stock')
                ->first();

            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            } else {
                $catalog->is_default = true;
            }
        }

        if ($catalog->isDirty('is_default') && $catalog->is_default === true) {
            $catalog->product->variants()
                ->where('id', '!=', $catalog->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
    }
}
