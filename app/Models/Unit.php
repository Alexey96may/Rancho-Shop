<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $short
 * @property string $slug
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductVariant> $productVariants
 * @property-read int|null $product_variants_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereShort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Unit extends Model
{
    protected $fillable = [
        'name',
        'short',
        'slug',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
