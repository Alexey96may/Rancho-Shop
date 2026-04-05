<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingBlocks extends Model
{
    protected $fillable = ['key', 'title', 'subtitle', 'content', 'is_visible'];

    protected $casts = [
        'content' => 'array', // JSON -> Array
        'is_visible' => 'boolean',
    ];

    // Quick search by the key
    public static function getByKey(string $key)
    {
        return self::where('key', $key)->where('is_visible', true)->first();
    }
}
