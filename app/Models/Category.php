<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
