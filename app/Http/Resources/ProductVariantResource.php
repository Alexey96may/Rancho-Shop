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
            'product_id' => $this->product_id,

            'name' => $this->name,

            'price' => $this->price,
            'price_rub' => $this->price / 100,

            'old_price' => $this->old_price,
            'old_price_rub' => $this->old_price ? $this->old_price / 100 : null,

            'stock' => $this->stock,

            'is_default' => $this->is_default,
            'position' => $this->position,

            'attributes' => $this->attributes,

            'unit' => [
                // 'name' => $this->unit->name,
                'short' => $this->unit->short,
                'slug' => $this->unit->slug,
            ],

            'amount' => $this->amount,

            'product' => [
                'name' => $this->product->name,
                'slug' => $this->product->slug,
            ],

            'media' => $this->product && $this->product->media->isNotEmpty()
                ? MediaResource::collection($this->product->media)
                : [MediaResource::fallback($this->product)],
        ];
    }
}
