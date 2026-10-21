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
        $default = $this->relationLoaded('defaultVariant')
        ? $this->defaultVariant
        : null;

        if (!$default && $this->relationLoaded('variants')) {
            $default = $this->variants->first();
        }

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,

            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_commentable' => (bool) $this->is_commentable,

            'availability' => $this->availability_type
                ? [
                    'value' => $this->availability_type->value,
                    'label' => $this->availability_type->label(),
                ]
                : null,

            'attributes' => $this->attributes,

            'main_photo' => $this->getMedia('main')->isNotEmpty()
                ? MediaResource::collection($this->getMedia('main'))
                : [MediaResource::fallback($this->resource)],

            'gallery' => $this->getMedia('gallery')->isNotEmpty()
                ? MediaResource::collection($this->getMedia('gallery'))
                : [],

            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'default_variant' => $default ? new ProductVariantResource($default) : null,

            'category' => new CategoryResource($this->whenLoaded('category')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
            'animals' => AnimalResource::collection($this->whenLoaded('animals')),

            'unit' => $default?->unit ? new UnitResource($default->unit) : null,

            'schedule' => $this->schedule,
            'next_delivery_date' => $this->next_delivery_date?->format('Y-m-d'),
        ];
    }
}
