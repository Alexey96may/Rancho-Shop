<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use App\Traits\HasInteractions;
use App\Traits\HasStandardMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
// Spatie
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasActiveScope, HasInteractions, HasStandardMedia, InteractsWithMedia, SoftDeletes {
        HasStandardMedia::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = [
        'category_id', 'name', 'slug', 'description',
        'availability_type', 'schedule', 'attributes', 'is_active',
    ];

    protected $casts = [
        'schedule' => 'array',
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Connection with an animals (for example, whose milk is this)
     */
    public function animals()
    {
        return $this->belongsToMany(Animal::class);
    }

    /**
     * Connection with a category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Connection with variants
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function mainVariant()
    {
        $default = $this->defaultVariant()->first();

        return $default ?? $this->variants->first();
    }

    /**
     * Setting up Spatie Media Library (v12 + Image v3)
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->useFallbackUrl('/images/no-product.jpg')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);
    }

    public function isInStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    public function isPurchasable(int $quantity = 1): bool
    {
        return $this->is_active
            && !$this->trashed()
            && $this->isInStock($quantity);
    }

}
