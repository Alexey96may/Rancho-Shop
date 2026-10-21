<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\UnitResource;
use Illuminate\Http\Request;

class AdminUnitResource extends UnitResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'created_at' => $this->created_at->format('d.m.Y H:i'),
            'product_variants_count' => $this->whenCounted('productVariants'),
            'can_delete' => (int) $this->product_variants_count === 0,
        ]);
    }
}
