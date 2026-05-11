<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

class AdminProductResource extends ProductResource
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
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            
            'variants_count' => $this->whenCounted('variants'),
            'comments_count' => $this->whenCounted('comments'),

            'rating_avg' => $this->when(
                !is_null($this->comments_avg_rating), 
                fn() => round((float) $this->comments_avg_rating, 1)
            ),
                
            // Status flags
            'is_trashed' => $this->trashed(),
            'can_delete' => !$this->trashed(),
        ]);
    }
}
