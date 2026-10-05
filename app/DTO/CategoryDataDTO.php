<?php

namespace App\DTO;

use App\Services\SlugService;

class CategoryDataDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $icon,
        public string $type,
        public ?string $description,
        public int $sort_order,
        public bool $is_active
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            name: $validated['name'],
            slug: SlugService::prepare($validated['slug'] ?? null, $validated['name']),
            icon: $validated['icon'] ?? null,
            type: $validated['type'],
            description: $validated['description'] ?? null,
            sort_order: (int) ($validated['sort_order'] ?? 0),
            is_active: (bool) ($validated['is_active'] ?? false)
        );
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'slug'        => $this->slug,
            'icon'        => $this->icon,
            'type'        => $this->type,
            'description' => $this->description,
            'sort_order'  => $this->sort_order,
            'is_active'   => $this->is_active,
        ];
    }
}
