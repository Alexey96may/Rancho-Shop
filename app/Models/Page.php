<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use App\Traits\HasInteractions;
use App\Traits\HasStandardMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Page extends Model
{
    use HasActiveScope, HasInteractions, HasStandardMedia;

    protected $fillable = ['title', 'slug', 'content', 'type', 'template', 'is_active'];

    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function reviews(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('content_images');
    }
}
