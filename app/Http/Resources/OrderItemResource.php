<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'product_name' => $this->product_name,

            'unit_price' => $this->unit_price,
            'old_unit_price' => $this->old_unit_price,

            'quantity' => $this->quantity,

            'unit' => [
                'name' => $this->unit_name,
                'code' => $this->unit_code,
            ],

            'subtotal' => $this->getSubtotalAttribute(),

            'created_at' => $this->created_at->format('d.m.Y H:i'),
            'updated_at' => $this->updated_at->format('d.m.Y H:i'),
        ];
    }
}
