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
use Illuminate\Database\Eloquent\Casts\Attribute;
// Spatie
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int $id
 * @property int $category_id
 * @property int|null $parent_id
 * @property string $name
 * @property string $type
 * @property string $slug
 * @property bool $is_active
 * @property string $status
 * @property string|null $bio
 * @property array<array-key, mixed>|null $features
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Animal> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read Animal|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \App\Models\Seo|null $seo
 * @property-read mixed $voice_url
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal cows()
 * @method static \Database\Factories\AnimalFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal withoutTrashed()
 * @mixin \Eloquent
 */
class Animal extends Model implements HasMedia
{
    use HasActiveScope, HasFactory, HasInteractions, HasStandardMedia, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'category_id',
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

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    protected $appends = ['voice_url'];

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
     * Connection with a category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Spatie Collection settings (to avoid confusing photos and audio)
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')
            ->useFallbackUrl('/images/no-animal.jpg')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('voice')
            ->acceptsMimeTypes(['audio/mpeg', 'audio/wav'])
            ->singleFile();
    }

    protected function voiceUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('voice') ?: null
        );
    }

    /**
    * Filter: cows only
    */
    public function scopeCows($query)
    {
        return $query->where('type', 'cow');
    }
}
