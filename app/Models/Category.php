<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string|null $description
 * @property string|null $icon
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Animal> $animals
 * @property-read int|null $animals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category foAnimals()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category forProducts()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category hasActiveAnimals()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category hasActiveProducts()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'sort_order', 'is_active', 'type', 'description'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Request only product categories (dairy, etc.)
    public function scopeHasActiveProducts($query)
    {
        return $query->whereHas('products', function ($q) {
            $q->where('is_active', true); 
        });
    }

    // Request only animal categories
    public function scopeHasActiveAnimals($query)
    {
        return $query->whereHas('animals', function ($q) {
            $q->where('is_active', true); 
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }

    public function scopeForProducts($query)
    {
        return $query->where('type', 'product');
    }

    
    public function scopeFoAnimals($query)
    {
        return $query->where('type', 'animals');
    }
}
