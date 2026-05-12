<?php

namespace App\Observers;

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
    public function updated(ProductVariant $productVariant): void
    {
        //
    }

    /**
     * Handle the ProductVariant "deleted" event.
     */
    public function deleted(ProductVariant $productVariant): void
    {
        //
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
    public function saving(ProductVariant $variant): void
    {
        if ($variant->isDirty('is_default') && $variant->is_default === true) {
            $variant->product->variants()
                ->where('id', '!=', $variant->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
    }
}
