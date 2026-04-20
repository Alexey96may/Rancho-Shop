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
            // 'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'bio' => $this->bio,
            'features' => $this->features,

            'voice_url' => $this->voice_url,

            'avatars' => $this->getMedia('avatars')->isNotEmpty()
                ? MediaResource::collection($this->getMedia('avatars'))
                : [MediaResource::fallback($this->resource)],

            'gallery' => MediaResource::collection($this->getMedia('gallery')),

            'category' => $this->whenLoaded('category', fn() => [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),

            'family' => [
                'parent' => $this->whenLoaded('parent', fn() => [
                    'name' => $this->parent->name,
                    'slug' => $this->parent->slug,
                ]),
                'children' => $this->whenLoaded('children', fn() => 
                    $this->children->map(fn($c) => ['name' => $c->name, 'slug' => $c->slug])
                ),
            ],

            'products' => ProductResource::collection(
                $this->whenLoaded('products')
            ),

            'seo' => new SeoResource($this->whenLoaded('seo')),
        ];
    }
}
