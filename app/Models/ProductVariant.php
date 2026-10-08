<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\ProductVariantObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
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
 * @method static Builder<static>|ProductVariant filter(array $filters)
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

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function isInStock(int $qty = 1): bool
    {
        return $this->stock >= $qty;
    }

    public function scopeInStock(Builder $query, int $qty = 1)
    {
        return $query->where('stock', '>=', $qty);
    }

    /**
     * pricing format
     */
    public function getPriceFormattedAttribute(): string
    {
        return number_format($this->price / 100, 2, '.', '');
    }

    /**
    * Scope for filtering and sorting product variants
    */
    public function scopeFilter(Builder $query, array $filters): void
    {
        // Поиск по имени варианта
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = mb_strtolower($search, 'UTF-8');
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
        });

        // Фильтр по наличию самого варианта
        if (isset($filters['in_stock']) && filter_var($filters['in_stock'], FILTER_VALIDATE_BOOLEAN)) {
            $query->where('stock', '>', 0);
        }

        // Сортировка вариантов по их прямой колонке price
        $sort = $filters['sort'] ?? null;

        if ($sort === 'cheap') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'expensive') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest();
        }
    }
}
