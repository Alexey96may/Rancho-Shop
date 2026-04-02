<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PromoCode extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 
        'max_discount', 'usage_limit', 'used_count', 
        'expires_at', 'is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
        'value'      => 'integer',
        'min_order_amount' => 'integer',
        'used_count' => 'integer',
    ];

    /**
    * Check up: is the promo code still valid?
    */
    public function isValid(): bool
    {
        if (!$this->is_active) return false;

        // Check by date
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        // Checking the usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;

        return true;
    }

    /**
    * Scope for quickly finding active codes
    */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)
              ->where(function ($q) {
                  $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
              });
    }
}
