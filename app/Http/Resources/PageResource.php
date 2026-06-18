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
            'content' => $this->when($request->routeIs('pages.show'), $this->content),
            'template' => $this->template,

            'url' => $this->url,

            'media' => $this->media->isNotEmpty() 
                        ? MediaResource::collection($this->media) 
                        : [MediaResource::fallback($this->resource)],

            'seo' => new SeoResource($this->whenLoaded('seo')),
            'reviews_count' => $this->whenCounted('reviews'),
        ];
    }
}
