<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

use App\Http\Resources\DeliveryAddressResource;

class AdminUserResource extends UserResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            // Specific data for the admin panel
            'google_id' => $this->google_id,
            'vkontakte_id' => $this->vkontakte_id,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            
            // Advanced statistics
            'orders_count' => $this->whenCounted('orders'),
            'comments_count' => $this->whenCounted('comments'),
            
            // Capability flags
            'is_staff' => $this->isStaff(),
            'can_manage_orders' => $this->canManageOrders(),

            // In the admin panel, addresses with all the details are often needed
            'addresses' => DeliveryAddressResource::collection($this->whenLoaded('deliveryAddresses')),
            
            'updated_at' => $this->updated_at->toIso8601String(),
        ]);
    }
}
