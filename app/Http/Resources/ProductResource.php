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
        $mainVariant = $this->relationLoaded('variants') ? $this->mainVariant() : null;

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,

            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            
            'availability' => $this->availability_type 
                ? [
                    'value' => $this->availability_type->value,
                    'label' => $this->availability_type->label(),
                ]
                : null,

            'schedule' => $this->schedule ?? [],

            'attributes' => $this->attributes,

            'main_photo' => $this->getMedia('main')->isNotEmpty()
                ? MediaResource::collection($this->getMedia('main'))
                : [MediaResource::fallback($this->resource)],

            'gallery' => $this->relationLoaded('media') && $this->media->isNotEmpty()
                ? MediaResource::collection($this->media)
                : [MediaResource::fallback($this->resource)],

            // whenLoaded
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
            'animals' => AnimalResource::collection($this->whenLoaded('animals')),

            'unit' => $mainVariant?->unit ? new UnitResource($mainVariant->unit) : null,

            'next_delivery_date' => $this->next_delivery_date?->format('Y-m-d'),
        ];
    }
}
