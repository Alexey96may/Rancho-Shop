<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Http\Resources\MediaResource;
use App\Http\Resources\AnimalResource as UserAnimalResource;

class AnimalResource extends UserAnimalResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'parent_id' => $this->parent_id,
            'category_id' => $this->category_id,
            'is_active' => $this->is_active,
            'media' => MediaResource::collection($this->getMedia('avatars')),

            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
        ]);
    }
}
