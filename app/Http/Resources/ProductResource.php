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
            'price_rub' => $this->price / 100, // Конвертируем копейки для витрины
            'unit' => $this->unit,
            'image' => $this->getFirstMediaUrl('gallery', 'thumbnail'),
            'is_available' => $this->stock > 0 || $this->availability_type === 'daily',
    ];
    }
}
