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

            'price_rub' => $this->price / 100,
            'old_price_rub' => $this->old_price ? $this->old_price / 100 : null,

            'stock' => $this->stock,

            'unit' => new UnitResource($this->whenLoaded('unit')),
        ];
    }
}
