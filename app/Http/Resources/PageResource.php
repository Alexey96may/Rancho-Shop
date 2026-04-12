<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
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

            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,

            'type' => $this->type,
            'is_active' => $this->is_active,

            // media (HasStandardMedia)
            'media' => $this->media->isNotEmpty() 
                        ? MediaResource::collection($this->media) 
                        : [MediaResource::fallback($this->resource)],

            // SEO
            'seo' => new SeoResource($this->whenLoaded('seo')),

            'reviews_count' => $this->whenCounted('reviews'),
        ];
    }
}
