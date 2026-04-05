<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'price_rub' => $this->price / 100, // Converting kopecks for the showcase
            'unit' => $this->unit,
            'is_available' => $this->stock > 0 || $this->availability_type === 'daily',

            'category' => new CategoryResource($this->whenLoaded('category')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
        ];
    }
}
