<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
//Spatie
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\ImageFormat;
use Spatie\Image\Enums\Fit;


class Product extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

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
            if (!request()->is('admin/*')) {
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
    * Polymorphic links (SEO and Reviews)
    */
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    protected function priceFormatted(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => number_format($this->price / 100, 2, '.', '')
        );
    }

    /** 
    * Setting up Spatie Media Library (v12 + Image v3) 
    */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->useFallbackUrl('/images/no-product.jpg');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 400, 400)
            ->format(ImageFormat::Avif)
            ->optimize()
            ->nonQueued();
    }
}
