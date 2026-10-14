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
        $isSiteReview = empty($this->commentable_type) && empty($this->commentable_id);
        $isDeletedMaterial = !$isSiteReview && $this->commentable === null;

        return [
            'id' => $this->id,
            'user_name' => $this->author_name,
            'guest_name' => $this->guest_name,
            'author_name' => $this->user?->name ?? $this->guest_name ?? 'Гость',
            'avatar' => $this->user?->avatar_url,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'content' => $this->content,
            'rating' => $this->rating,

            'is_site_review' => $isSiteReview,
            'is_deleted_material' => $isDeletedMaterial,
            'commentable' => $this->commentable ? [
                'id' => $this->commentable->id,
                'name' => $this->commentable->name
                    ?? $this->commentable->title
                    ?? null,
                'type' => class_basename($this->commentable),
                'slug' => $this->commentable->slug ?? null,
                'url' => $this->commentableUrl($this->commentable),
                'is_commentable' => (bool) ($this->commentable->is_commentable ?? true),
            ] : null,

            'created_at' => $this->created_at->toIso8601String(),
        ];
    }

    protected function commentableUrl($commentable): ?string
    {
        if (empty($commentable->slug)) {
            return null;
        }

        return match (class_basename($commentable)) {
            'Product' => route('catalog.show', $commentable->slug),
            'Animal' => route('animals.show', $commentable->slug),
            'Page' => route('pages.show', $commentable->slug),
            default => null,
        };
    }
}
