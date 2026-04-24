<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Http\Resources\CommentResource;

class AdminCommentResource extends CommentResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'is_published' => $this->is_published,
            'updated_at'   => $this->updated_at->format('d.m.Y H:i'),
            'commentable'  => $this->whenLoaded('commentable', function() {
                return [
                    'name' => $this->commentable?->name ?? 'Сайт',
                    'slug' => $this->commentable?->slug ?? 'main',
                    'type' => $this->commentable_type,
                ];
            }),
        ]);
    }
}
