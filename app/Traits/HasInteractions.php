<?php

namespace App\Traits;

use App\Models\Seo;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasInteractions
{
    /**
    * Polymorphic connection with SEO (via our 'animal' MorphMap)
    */
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    /**
    * Polymorphic relationship with comments
    */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}