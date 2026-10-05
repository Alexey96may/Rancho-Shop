<?php

namespace App\DTO;

use App\Models\Unit;

class UnitDataDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public string $short,
        public string $slug,
        public int $position
    ) {}

    public static function fromRequest(array $validated, ?int $currentId = null): self
    {
        // If an empty slug is received, we take the short name 'short' as a basis
        $slugSource = !empty($validated['slug']) ? $validated['slug'] : $validated['short'];
        
        return new self(
            name: $validated['name'],
            short: $validated['short'],
            slug: Unit::generateUniqueSlug($slugSource, $currentId),
            position: (int) ($validated['position'] ?? 0)
        );
    }

    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'short'    => $this->short,
            'slug'     => $this->slug,
            'position' => $this->position,
        ];
    }
}
