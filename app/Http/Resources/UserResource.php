<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone ?? 'Не указан',
            'role' => $this->role->value,
            'avatar' => $this->avatar_url,
            'is_admin' => $this->isAdmin(),
            'orders_count' => $this->whenCounted('orders'),
            'addresses' => $this->whenLoaded('deliveryAddresses'),
            'created_at' => $this->created_at->format('d.m.Y'),
        ];
    }
}
