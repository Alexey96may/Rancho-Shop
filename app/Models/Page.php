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
