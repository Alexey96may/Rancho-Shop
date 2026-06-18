<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Enums\LandingBlockKey;
use App\Http\Resources\LandingBlockResource;

class AdminLandingBlockResource extends LandingBlockResource
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
            'id'         => $this->id,
            'is_visible' => (bool) $this->is_visible,
            'label'      => ($this->key instanceof LandingBlockKey)
                ? $this->key->label() 
                : $this->key,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ]);
    }
}
