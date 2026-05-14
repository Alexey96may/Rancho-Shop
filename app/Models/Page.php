<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use App\Traits\HasInteractions;
use App\Traits\HasStandardMedia;
use App\Enums\PageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $content
 * @property PageType $type
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $template
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $reviews
 * @property-read int|null $reviews_count
 * @property-read \App\Models\Seo|null $seo
 * @property-read mixed $url
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Page extends Model implements HasMedia
{
    use HasActiveScope, HasInteractions, HasStandardMedia;

    protected $fillable = ['title', 'slug', 'content', 'type', 'template', 'is_active'];

    protected $appends = ['url'];

    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    protected $casts = [
        'is_active' => 'boolean',
        'type' => PageType::class,
    ];

    public function reviews(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('content_images');
    }

    public function isDeletable(): bool
    {
        return $this->type !== PageType::SYSTEM && $this->type !== PageType::HOME;
    }

    public function url(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type === PageType::HOME
                ? route('home') 
                : route('pages.show', $this->slug)
        );
    }
}
