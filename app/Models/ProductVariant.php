<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\ProductVariantObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    /**
    * Scope for filtering and sorting product variants
    */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($query, $search) {
                $searchTerm = mb_strtolower($search, 'UTF-8');

                $query->where(function ($q) use ($searchTerm) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"])
                      ->orWhere('id', 'like', "%{$searchTerm}%")
                      ->orWhereHas('product', function ($pq) use ($searchTerm) {
                          $pq->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"]);
                      });
                });
            })
            ->when($filters['product_id'] ?? null, fn($q, $id) => $q->where('product_id', $id))
            ->when($filters['unit_id'] ?? null, fn($q, $id) => $q->where('unit_id', $id))
            ->when($filters['sort'] ?? null, function ($q, $sort) {
                switch ($sort) {
                    case 'price_asc':  $q->orderBy('price', 'asc'); break;
                    case 'price_desc': $q->orderBy('price', 'desc'); break;
                    case 'stock_desc': $q->orderBy('stock', 'desc'); break;
                    case 'newest':     $q->latest(); break;
                    default:           $q->orderBy('position', 'asc');
                }
            }, fn($q) => $q->orderBy('position', 'asc'));
    }
}
