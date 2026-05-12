<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\ProductVariantObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(ProductVariantObserver::class)]
class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'unit_id',
        'name',
        'price',
        'old_price',
        'stock',
        'is_default',
        'position',
        'attributes',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'integer',
        'old_price' => 'integer',
        'stock' => 'integer',
        'is_default' => 'boolean',
    ];

    /**
     * belongs to the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * has a unit of measurement
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function priceRub(): float
    {
        return $this->price / 100;
    }

    public function isInStock(int $qty = 1): bool
    {
        return $this->stock >= $qty;
    }

    /**
     * pricing format
     */
    public function getPriceFormattedAttribute(): string
    {
        return number_format($this->price / 100, 2, '.', '');
    }
}
