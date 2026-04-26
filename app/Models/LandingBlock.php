<?php

namespace App\Models;

use App\Enums\LandingBlockKey;
use Illuminate\Database\Eloquent\Model;

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
