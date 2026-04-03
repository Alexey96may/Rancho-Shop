<?php

namespace App\Models;

use App\Traits\HasInteractions;
use App\Traits\HasStandardMedia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
//Spatie
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\ImageFormat;
use Spatie\Image\Enums\Fit;


class Product extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, HasStandardMedia, HasInteractions;

    protected $fillable = [
        'animal_id', 'name', 'slug', 'description', 
        'old_price', 'price', 'unit', 'stock', 
        'availability_type', 'schedule', 'attributes', 'is_active'
    ];

    protected $casts = [
        'schedule' => 'array',
        'attributes' => 'array',
        'is_active' => 'boolean',
        'price' => 'integer',
        'old_price' => 'integer',
    ];

    /**
     * Global Scope: Hide inactive products for everyone except the admin panel.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            if (!request()->is('admin/*') && !request()->is('api/admin/*')) {
                $builder->where('is_active', true);
            }
        });
    }

    /**
    * Connection with an animal (for example, whose milk is this)
    */
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    /**
    * Accessor for displaying prices in a beautiful way.
    * Store 1500 (int), get 15.00
    */
    protected function priceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->price / 100, 2, '.', '')
        );
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
