<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'sort_order', 'is_active', 'type'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Request only product categories (dairy, etc.)
    public function scopeProducts($query)
    {
        return $query->where('type', 'product');
    }

    // Request only animal categories
    public function scopeAnimals($query)
    {
        return $query->where('type', 'animal');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }
}
