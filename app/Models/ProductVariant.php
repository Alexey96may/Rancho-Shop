<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\ProductVariantObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * @property int $id
 * @property int $product_id
 * @property int $unit_id
 * @property string $name
 * @property int $price
 * @property int|null $old_price
 * @property int $stock
 * @property bool $is_default
 * @property int $position
 * @property array<array-key, mixed>|null $attributes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $price_formatted
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Unit $unit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereAttributes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereOldPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
