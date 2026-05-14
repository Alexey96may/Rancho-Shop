<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use App\Traits\HasInteractions;
use App\Traits\HasStandardMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\AvailabilityType;
// Spatie
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property int|null $category_id
 * @property int|null $animal_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property AvailabilityType $availability_type
 * @property array<array-key, mixed>|null $schedule
 * @property array<array-key, mixed>|null $attributes
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Animal> $animals
 * @property-read int|null $animals_count
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\ProductVariant|null $defaultVariant
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Seo|null $seo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductVariant> $variants
 * @property-read int|null $variants_count
 * @method static Builder<static>|Product active()
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static Builder<static>|Product filter(array $filters)
 * @method static Builder<static>|Product newModelQuery()
 * @method static Builder<static>|Product newQuery()
 * @method static Builder<static>|Product onlyTrashed()
 * @method static Builder<static>|Product query()
 * @method static Builder<static>|Product whereAnimalId($value)
 * @method static Builder<static>|Product whereAttributes($value)
 * @method static Builder<static>|Product whereAvailabilityType($value)
 * @method static Builder<static>|Product whereCategoryId($value)
 * @method static Builder<static>|Product whereCreatedAt($value)
 * @method static Builder<static>|Product whereDeletedAt($value)
 * @method static Builder<static>|Product whereDescription($value)
 * @method static Builder<static>|Product whereId($value)
 * @method static Builder<static>|Product whereIsActive($value)
 * @method static Builder<static>|Product whereName($value)
 * @method static Builder<static>|Product whereSchedule($value)
 * @method static Builder<static>|Product whereSlug($value)
 * @method static Builder<static>|Product whereUpdatedAt($value)
 * @method static Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Product withoutTrashed()
 * @mixin \Eloquent
 */
class Product extends Model implements HasMedia
{
    use HasActiveScope, HasFactory, HasInteractions, HasStandardMedia, InteractsWithMedia, SoftDeletes {
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
        'availability_type' => AvailabilityType::class,
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

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = mb_strtolower($search, 'UTF-8');
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        })->when($filters['category'] ?? null, function ($query, $category) {
            $query->where('category_id', $category);
        })->when($filters['animal'] ?? null, function ($query, $animal) {
            $query->whereHas('animals', function ($q) use ($animal) {
                $q->where('animals.id', $animal);
            });
        });
    }

    /**
     * Setting up Spatie Media Library (v12 + Image v3)
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main')
            ->useFallbackUrl('/images/no-product.jpg')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();

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
