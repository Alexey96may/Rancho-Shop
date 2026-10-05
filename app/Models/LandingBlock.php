<?php

namespace App\Models;

use App\Enums\LandingBlockKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property LandingBlockKey $key
 * @property string $title
 * @property string|null $subtitle
 * @property array<array-key, mixed> $content
 * @property bool $is_visible
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereIsVisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LandingBlock whereUpdatedAt($value)
 * @method static Builder<static>|LandingBlock filter(array $filters)
 * @mixin \Eloquent
 */
class LandingBlock extends Model
{
    protected $fillable = ['key', 'title', 'subtitle', 'content', 'is_visible'];

    protected $casts = [
        'content' => 'array', // JSON -> Array
        'is_visible' => 'boolean',
        'key' => LandingBlockKey::class,
    ];

    /**
    * Returns a block by key or an empty placeholder object.
    */
    public static function getSafe(string $key): self
    {
        return self::where('key', $key)
            ->where('is_visible', true)
            ->first() ?? self::make([
                'key' => $key,
                'title' => '',
                'subtitle' => '',
                'content' => [],
            ]);
    }

    // Quick search by the key
    public static function getByKey(string $key)
    {
        return self::where('key', $key)->where('is_visible', true)->first();
    }

    /**
    * Scope for case-insensitive searching by enum name and labels
    */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query->when($filters['search'] ?? null, function ($query, $search) {
            $search = mb_strtolower($search, 'UTF-8');

            $matchingKeys = collect(LandingBlockKey::cases())
                ->filter(fn($case) => str_contains(
                    mb_strtolower($case->label(), 'UTF-8'), 
                    $search
                ))
                ->map(fn($case) => $case->value);

            $query->where(function ($q) use ($search, $matchingKeys) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                  ->orWhereIn('key', $matchingKeys);
            });
        });
    }
}
