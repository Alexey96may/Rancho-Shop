<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use App\Traits\HasInteractions;
use App\Traits\HasStandardMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'animal_id', 'name', 'slug', 'description',
        'old_price', 'price', 'unit', 'stock',
        'availability_type', 'schedule', 'attributes', 'is_active',
    ];

    protected $casts = [
        'schedule' => 'array',
        'attributes' => 'array',
        'is_active' => 'boolean',
        'price' => 'integer',
        'old_price' => 'integer',
    ];

    protected $appends = ['main_image', 'price_formatted'];

    /**
     * Connection with an animal (for example, whose milk is this)
     */
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    /**
     * Connection with an category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Accessory for the main image (Spatie)
     * To simply write :src="product.main_image" in Vue
     */
    protected function mainImage(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('gallery', 'preview') 
                ?: '/images/no-product.jpg'
        );
    }

    /**
     * Accessor for displaying prices in a beautiful way.
     * Store 1500 (int), get 15.00
     */
    protected function priceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => number_format($attributes['price'] / 100, 2, '.', '')
        )->shouldCache();
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
}
