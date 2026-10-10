<?php

namespace App\Models;

use App\Enums\AvailabilityType;
use App\Traits\HasActiveScope;
use App\Traits\HasInteractions;
use App\Traits\HasSeoActions;
use App\Traits\HasStandardMedia;
use App\Traits\Models\HasAdminTrash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// Spatie
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Animal> $animals
 * @property-read int|null $animals_count
 * @property-read Category|null $category
 * @property-read Collection<int, Comment> $comments
 * @property-read int|null $comments_count
 * @property-read ProductVariant|null $defaultVariant
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Seo|null $seo
 * @property-read Collection<int, ProductVariant> $variants
 * @property-read int|null $variants_count
 *
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
 *
 * @mixin \Eloquent
 */
class Product extends Model implements HasMedia
{
    use HasActiveScope, HasAdminTrash, HasFactory, HasInteractions, HasSeoActions, HasStandardMedia, InteractsWithMedia, SoftDeletes {
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

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($query, $search) {
                $search = mb_strtolower($search, 'UTF-8');
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($filters['category'] ?? null, function ($query, $category) {
                $query->whereHas('category', function ($q) use ($category) {
                    if (is_numeric($category)) {
                        $q->where('id', $category);
                    } else {
                        $q->where('slug', $category);
                    }
                });
            })
            ->when($filters['animal'] ?? null, function ($query, $animal) {
                $query->whereHas('animals', function ($q) use ($animal) {
                    $q->where('animals.id', $animal);
                });
            })
            ->when(
                !empty($filters['in_stock']) && filter_var($filters['in_stock'], FILTER_VALIDATE_BOOLEAN),
                function ($query) {
                    $query->whereHas('variants', function ($q) {
                        $q->where('is_default', true)->where('stock', '>', 0);
                    });
                }
            );
    }

    /**
     * Scope for retrieving in-stock products based on the default variant
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereHas('variants', function ($q) {
            $q->where('is_default', true)
                ->where('stock', '>', 0);
        });
    }

    public function scopeSort(Builder $query, ?string $sort = null): Builder
    {
        // 1. in_stock сверху — только для каталога
        $inStockSubQuery = ProductVariant::selectRaw('CASE WHEN stock > 0 THEN 1 ELSE 0 END')
            ->whereColumn('product_id', 'products.id')
            ->where('is_default', true)
            ->limit(1);

        $query->orderByRaw(
            'COALESCE((' . $inStockSubQuery->toSql() . '), 0) DESC',
            $inStockSubQuery->getBindings()
        );

        // 2. Пользовательская сортировка
        if ($sort === 'cheap') {
            $query->withMin('variants', 'price')->orderBy('variants_min_price', 'asc');
        } elseif ($sort === 'expensive') {
            $query->withMax('variants', 'price')->orderBy('variants_max_price', 'desc');
        } else {
            $query->orderByDesc('products.created_at');
        }

        // 3. Тайбрейкер — иначе записи «прыгают»
        return $query->orderByDesc('products.id');
    }

    public function scopeWithStockFlag(Builder $query): Builder
    {
        return $query->addSelect([
            'has_default_in_stock' => ProductVariant::selectRaw('CASE WHEN stock > 0 THEN 1 ELSE 0 END')
                ->whereColumn('product_id', 'products.id')
                ->where('is_default', true)
                ->limit(1),
        ]);
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
        return ($this->defaultVariant?->stock ?? 0) >= $quantity;
    }

    public function isPurchasable(int $quantity = 1): bool
    {
        return $this->is_active
            && !$this->trashed()
            && $this->isInStock($quantity);
    }
}
