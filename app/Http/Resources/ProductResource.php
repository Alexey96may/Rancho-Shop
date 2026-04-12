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
        //Product::with(['variants.unit', 'category', 'seo', 'media'])\
        $variant = $this->relationLoaded('variants')
            ? $this->variants->first()
            : null;

        return [
            'id' => $this->id,
            'animal_id' => $this->animal_id,
            'category_id' => $this->category_id,

            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            
            'availability_type' => $this->availability_type,
            
            'schedule' => $this->schedule,
            'next_delivery_date' => $this->next_delivery_date?->format('Y-m-d'),

            'attributes' => $this->attributes,

            'price_rub' => $variant ? $variant->price / 100 : null,
            'old_price_rub' => $variant?->old_price ? $variant->old_price / 100 : null,

            'media' => $this->relationLoaded('media') && $this->media->isNotEmpty()
                ? MediaResource::collection($this->media)
                : [MediaResource::fallback($this->resource)],

            // whenLoaded
            'variants' => ProductVariantResource::collection(
                $this->whenLoaded('variants')
            ),
            
            'unit' => $variant?->unit
                ? new UnitResource($variant->unit)
                : null,

            'category' => new CategoryResource($this->whenLoaded('category')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
        ];
    }
}
