<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\CommentStatus;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'guest_name',
        'content',
        'rating',
        'status',
        'commentable_id',
        'commentable_type',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'status' => CommentStatus::class,
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
        $query->where('status', CommentStatus::APPROVED);
    }
}
