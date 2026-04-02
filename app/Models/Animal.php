<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

//Spatie
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Animal extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'parent_id',
        'name',
        'type',
        'slug',
        'is_active',
        'status',
        'bio',
        'features',
    ];

    /**
    * Automatic type conversion.
    * JSON from the database immediately becomes an array in PHP.
    */
    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    /**
    * Relationship to a parent (e.g., a cow's mother)
    */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->HasMany(Animal::class, 'parent_id');
    }

    /**
    * Polymorphic connection with SEO (via our 'animal' MorphMap)
    */
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    /**
    * Polymorphic relationship with comments
    */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    //Spatie

    /**
    * Registration of collections and conversions (photo processing)
    */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Create a thumbnail for the cow card (preview)
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();

        // Conversion for a large photo in a gallery
        $this->addMediaConversion('preview')
            ->fit(Fit::Max, 1200, 1200)
            ->optimize()
            ->withResponsiveImages();
    }

    /**
    * Collection settings (to avoid confusing photos and audio)
    */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('voice')
            ->acceptsMimeTypes(['audio/mpeg', 'audio/wav'])
            ->singleFile();
    }

}
