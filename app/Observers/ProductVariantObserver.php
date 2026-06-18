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
    public function deleted(ProductVariant $catalog): void
    {
        // If the deleted variant was the default one
        if ($catalog->is_default) {
            // We are looking for a new candidate (maximum stock).
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
        // If this is the product's only variant, it MUST be the default one.
        if ($catalog->product->variants()->count() === 0) {
            $catalog->is_default = true;
            return;
        }

        // 2. If we are attempting to remove the default status (is_default changed from true to false)
        if ($catalog->isDirty('is_default') && $catalog->is_default === false && $catalog->getOriginal('is_default') === true) {
            // Best in stock, excluding the current one
            $newDefault = $catalog->product->variants()
                ->where('id', '!=', $catalog->id)
                ->orderByDesc('stock')
                ->first();

            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            } else {
                // If there is no one to pass it to (the only scenario), prevent removing the default.
                $catalog->is_default = true;
            }
        }

        // If this option becomes the default, reset the default status for the others (your old logic).
        if ($catalog->isDirty('is_default') && $catalog->is_default === true) {
            $catalog->product->variants()
                ->where('id', '!=', $catalog->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
    }
}
