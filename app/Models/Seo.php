<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $seoable_type
 * @property int $seoable_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $keywords
 * @property string|null $canonical
 * @property array<array-key, mixed>|null $og_data
 * @property bool $is_noindex
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $seoable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereCanonical($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereIsNoindex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereOgData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereSeoableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereSeoableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Seo extends Model
{
    protected $fillable = [
        'title',
        'description',
        'keywords',
        'canonical',
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
