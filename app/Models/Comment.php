<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'guest_name',
        'content', 
        'rating', 
        'is_published', 
        'commentable_id', 
        'commentable_type'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_published' => 'boolean',
        'rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function authorName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user ? $this->user->name : $this->guest_name
        );
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
    * Scope for displaying only admin-approved reviews
    */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }
}
