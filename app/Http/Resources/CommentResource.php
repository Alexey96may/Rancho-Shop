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
        // Base structure
        $data = [
            'id'               => $this->id,
            'user_name'        => $this->author_name ?? 'Гость',
            'avatar'           => $this->user?->avatar_url,
            'content'          => $this->content,
            'rating'           => $this->rating,
            'user_avatar'      => $this->user?->avatar_url,
            'commentable_id'   => $this->commentable_id,
            'commentable_type' => $this->commentable_type,
            'created_at'       => $this->created_at->format('d.m.Y H:i'),
        ];

        // (Interface AdminComment)
        $this->mergeWhen($request->user()?->is_admin, [
            'is_published' => $this->is_published,
            'updated_at'   => $this->updated_at->format('d.m.Y H:i'),
            // Используем whenLoaded для полиморфной связи
            'commentable'  => $this->whenLoaded('commentable', function() {
                return [
                    'name' => $this->commentable?->name ?? 'Сайт',
                    'slug' => $this->commentable?->slug ?? 'main',
                    'type' => $this->commentable_type,
                ];
            }),
        ]);

        return $data;
    }
}
