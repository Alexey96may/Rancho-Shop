<?php

namespace App\Actions\Comments;

use App\Enums\CommentableType;
use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\User;

class CreateCommentAction
{
    public function handle(array $data, ?User $user): Comment
    {
        $status = $user?->isAdmin()
            ? CommentStatus::APPROVED
            : CommentStatus::PENDING;

        $type = isset($data['commentable_type'])
            ? CommentableType::from($data['commentable_type'])
            : null;

        return Comment::create([
            'commentable_id' => $data['commentable_id'] ?? null,
            'commentable_type' => $type,
            'content' => $data['content'],
            'rating' => $data['rating'] ?? null,
            'user_id' => $user?->id,
            'guest_name' => $user ? null : $data['guest_name'],
            'status' => $status,
        ]);
    }
}
