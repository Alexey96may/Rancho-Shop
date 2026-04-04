<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Seo extends Model
{
    protected $fillable = [
        'title',
        'description',
        'keywords',
        'og_data',
        'is_noindex',
        'seoable_id',
        'seoable_type',
    ];

    protected $casts = [
        'og_data' => 'array',
        'is_noindex' => 'boolean',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
