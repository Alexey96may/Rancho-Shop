<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'user_name'        => $this->author_name ?? 'Гость',
            'avatar'           => $this->user?->avatar_url,
            'status'           => $this->status->value,
            'status_label'     => $this->status->label(),
            'content'          => $this->content,
            'rating'           => $this->rating,
            'commentable_id'   => $this->commentable_id,
            'commentable_type' => $this->commentable_type,
            'created_at'       => $this->created_at->format('d.m.Y H:i'),
        ];
    }
}
