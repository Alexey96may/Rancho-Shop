<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
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

            'price' => $this->price,
            'old_price' => $this->old_price,

            'stock' => $this->stock,
            'is_in_stock' => $this->isInStock(),
            'is_default' => $this->is_default,

            'attributes' => $this->attributes,

            'unit' => $this->whenLoaded('unit', function() {
                return [
                    'short' => $this->unit->short,
                    'slug' => $this->unit->slug,
                    'name' => $this->unit->name,
                ];
            }),
        ];
    }
}
