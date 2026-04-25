<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'customer_phone' => $this->customer_phone,
            'customer_comment' => $this->customer_comment,
            'admin_note' => $this->admin_note,
            'updated_at' => $this->updated_at->toIso8601String(),
        ]);
    }
}
