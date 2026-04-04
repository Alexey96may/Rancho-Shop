<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use App\Traits\HasInteractions;
use App\Traits\HasStandardMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
// Spatie
use Spatie\MediaLibrary\HasMedia;

class Animal extends Model implements HasMedia
{
    use HasActiveScope, HasFactory, HasInteractions, HasStandardMedia, SoftDeletes;

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

    /**
     * Relationship to a children (e.g., a cow's children)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Animal::class, 'parent_id');
    }

    /**
     * Spatie Collection settings (to avoid confusing photos and audio)
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->useFallbackUrl('/images/no-animal.jpg')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('voice')
            ->acceptsMimeTypes(['audio/mpeg', 'audio/wav'])
            ->singleFile();
    }
}
