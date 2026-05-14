<?php

namespace App\Models;

use App\Enums\LandingBlockKey;
use Illuminate\Database\Eloquent\Model;

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
}
