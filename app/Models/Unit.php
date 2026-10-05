<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $short
 * @property string $slug
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductVariant> $productVariants
 * @property-read int|null $product_variants_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereShort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Unit extends Model
{
    protected $fillable = [
        'name',
        'short',
        'slug',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = mb_strtolower($search, 'UTF-8');
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(short) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$search}%"]);
            });
        });
    }

    /**
    * Generate a guaranteed unique slug for a unit of measurement
    */
    public static function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $slug = Str::customSlug($source);
        
        // We search for exact matches of a slug or slugs with suffixes (for example, kg, kg-1, kg-2)
        $existingSlugs = self::where(function($q) use ($slug) {
                $q->where('slug', $slug)
                  ->orWhere('slug', 'LIKE', "{$slug}-%");
            })
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->pluck('slug')
            ->toArray();

        if (!in_array($slug, $existingSlugs)) {
            return $slug;
        }

        // If already exists, iteratively select a free index
        $i = 1;
        while (in_array("{$slug}-{$i}", $existingSlugs)) {
            $i++;
        }

        return "{$slug}-{$i}";
    }
}
