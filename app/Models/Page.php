<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Page extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'type', 'is_active'];

    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
