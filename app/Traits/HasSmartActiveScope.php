<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSmartActiveScope
{
    /**
    * Laravel magic: boot method + TraitName
    */
    public function bootHasSmartActiveScope(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            // Check if this is an admin panel
            if (!request()->is('admin/*') && !request()->is('api/admin/*')) {
                $builder->where('is_active', true);
            }
        });
    }

    /**
    * Standard scope for manual invocation: PromoCode::active()->get()
    */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
    * Allows you to force the display of deleted/hidden items in the admin panel:
    * Product::withoutGlobalScope('active')->get();
    */
}