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
            'animal_id' => $this->animal_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,

            'price_rub' => $this->price / 100, // Converting kopecks for the showcase
            'old_price' => $this->old_price,

            'unit' => $this->unit,
            'stock' => $this->stock,
            'availability_type' => $this->availability_type,

            'schedule' => $this->schedule,
            'next_delivery_date' => $this->next_delivery_date?->format('Y-m-d'),

            'attributes' => $this->attributes,

            'media' => $this->media->isNotEmpty() 
                        ? MediaResource::collection($this->media) 
                        : [MediaResource::fallback($this->resource)],

            'category' => new CategoryResource($this->whenLoaded('category')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
        ];
    }
}
