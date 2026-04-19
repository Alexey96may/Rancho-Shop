<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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

            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            // DELIVERY SNAPSHOT
            'delivery_address' => $this->delivery_address,
            'delivery_lat' => $this->delivery_lat,
            'delivery_lng' => $this->delivery_lng,

            'is_pickup' => (bool) $this->is_pickup,
            'delivery_validated' => (bool) $this->delivery_validated,

            // 🔥 IMPORTANT: metadata snapshot
            'delivery_meta' => $this->delivery_meta,

            'total_price' => $this->total_price,
            'delivery_price' => $this->delivery_price,
            'discount_total' => $this->discount_total,

            'status' => $this->status,

            'promo_code_id' => $this->promo_code_id,

            'created_at' => $this->created_at->format('d.m.Y H:i'),
            'total_items' => $this->items->sum('quantity'),

            // 👇 связь
            'items' => OrderItemResource::collection(
                $this->whenLoaded('items')
            ),

            'user' => $this->when($this->relationLoaded('user') && $this->user, function() {
                return new UserResource($this->user);
            }),
        ];
    }
}
