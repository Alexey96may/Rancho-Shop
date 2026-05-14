<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\CommentStatus;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $guest_name
 * @property string $content
 * @property int|null $rating
 * @property CommentStatus $status
 * @property string|null $commentable_type
 * @property int|null $commentable_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read mixed $author_name
 * @property-read Model|\Eloquent|null $commentable
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\CommentFactory factory($count = null, $state = [])
 * @method static Builder<static>|Comment newModelQuery()
 * @method static Builder<static>|Comment newQuery()
 * @method static Builder<static>|Comment published()
 * @method static Builder<static>|Comment query()
 * @method static Builder<static>|Comment whereCommentableId($value)
 * @method static Builder<static>|Comment whereCommentableType($value)
 * @method static Builder<static>|Comment whereContent($value)
 * @method static Builder<static>|Comment whereCreatedAt($value)
 * @method static Builder<static>|Comment whereDeletedAt($value)
 * @method static Builder<static>|Comment whereGuestName($value)
 * @method static Builder<static>|Comment whereId($value)
 * @method static Builder<static>|Comment whereRating($value)
 * @method static Builder<static>|Comment whereStatus($value)
 * @method static Builder<static>|Comment whereUpdatedAt($value)
 * @method static Builder<static>|Comment whereUserId($value)
 * @mixin \Eloquent
 */
class Comment extends Model
{
    use HasFactory;

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
