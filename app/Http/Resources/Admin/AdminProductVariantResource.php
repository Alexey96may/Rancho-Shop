<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Http\Resources\ProductVariantResource;
use App\Http\Resources\MediaResource;

class AdminProductVariantResource extends ProductVariantResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base = parent::toArray($request);

        return array_merge($base, [
            'product_id' => $this->product_id,
            'unit_id' => $this->unit_id,
            
            'position' => $this->position,
            'created_at' => $this->created_at?->toIso8601String(),

            'unit' => AdminUnitResource::make($this->whenLoaded('unit')),
            
            'product' => $this->whenLoaded('product', function() {
                return [
                    'id'   => $this->product->id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                ];
            }),

            'media' => $this->whenLoaded('product', function() {
                 return $this->product->media->isNotEmpty()
                    ? MediaResource::collection($this->product->media)
                    : [MediaResource::fallback($this->product)];
            }),
        ]);
    }
}
