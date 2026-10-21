<?php

namespace App\DTO;

use App\Models\Animal;
use App\Services\SlugService;

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
        public ?string $status,
        public ?string $bio,
        public array $features,
        public bool $is_commentable,
        public bool $is_active,
        public array $seoData
    ) {}

    public static function fromRequest(array $validated, ?int $ignoreId = null): self
    {
        return new self(
            name: $validated['name'],
            slug: SlugService::prepare(
                $validated['slug'] ?? null,
                $validated['name'],
                Animal::class,
                $ignoreId,
            ),
            category_id: (int) $validated['category_id'],
            parent_id: !empty($validated['parent_id']) ? (int) $validated['parent_id'] : null,
            status: $validated['status'] ?? null,
            bio: $validated['bio'] ?? null,
            features: array_values(array_filter(
                $validated['features'] ?? [],
                fn ($a) => !empty($a['label'])
            )),
            is_commentable: (bool) ($validated['is_commentable'] ?? true),
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
            'is_commentable' => $this->is_commentable,
            'bio' => $this->bio,
            'features' => $this->features,
            'is_active' => $this->is_active,
        ];
    }
}
