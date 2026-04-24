<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Http\Resources\OrderResource as UserOrderResource;

class OrderResource extends UserOrderResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        return array_merge($data, [
            'delivery_meta' => $this->delivery_meta,
            'admin_note' => $this->admin_note,
        ]);
    }
}
