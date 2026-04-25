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
            'updated_at'   => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
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
