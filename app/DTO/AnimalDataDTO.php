<?php

namespace App\DTO;

use Illuminate\Support\Str;

class AnimalDataDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public string $slug,
        public int $category_id,
        public ?int $parent_id,
        public string $status,
        public ?string $bio,
        public array $features,
        public bool $is_active,
        public array $seoData
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            name: $validated['name'],
            slug: Str::slug($validated['name']),
            category_id: (int) $validated['category_id'],
            parent_id: !empty($validated['parent_id']) ? (int) $validated['parent_id'] : null,
            status: $validated['status'],
            bio: $validated['bio'] ?? null,
            features: $validated['features'] ?? [],
            is_active: (bool) ($validated['is_active'] ?? false),
            seoData: $validated['seo'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'category_id' => $this->category_id,
            'parent_id' => $this->parent_id,
            'status' => $this->status,
            'bio' => $this->bio,
            'features' => $this->features,
            'is_active' => $this->is_active,
        ];
    }
}
