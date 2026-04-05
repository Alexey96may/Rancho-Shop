<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnimalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'slug' => $this->slug,
            'bio' => $this->bio,
            'features' => $this->features,

            'media' => MediaResource::collection($this->whenLoaded('media')),

            'voice_url' => $this->media->first(fn($m) => str_contains($m->mime_type, 'audio'))?->getUrl(),

            'parent' => $this->whenLoaded('parent', function() {
                return [
                    'name' => $this->parent->name,
                    'slug' => $this->parent->slug,
                ];
            }),
            
            'children' => $this->whenLoaded('children', function() {
                return $this->children->map(fn($child) => [
                    'name' => $child->name,
                    'slug' => $child->slug,
                ]);
            }),

            'seo' => new SeoResource($this->whenLoaded('seo')),
        ];
    }
}
