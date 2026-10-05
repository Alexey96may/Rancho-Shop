<?php

namespace App\DTO;

use App\Enums\AvailabilityType;
use App\Services\SlugService;

class ProductDataDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public string $slug,
        public int $category_id,
        public AvailabilityType $availability_type,
        public bool $is_active,
        public ?string $description,
        public array $attributes,
        public array $schedule,
        public array $animal_ids,
        public array $seoData
    ) {}

    /**
    * Factory method for assembling a DTO from validated request data
    */
    public static function fromRequest(array $validated): self
    {

        return new self(
            name: $validated['name'],
            slug: SlugService::prepare($validated['slug'] ?? null, $validated['name']),
            category_id: (int) $validated['category_id'],
            availability_type: $validated['availability_type'] instanceof AvailabilityType 
                ? $validated['availability_type'] 
                : AvailabilityType::from($validated['availability_type']),
            is_active: (bool) ($validated['is_active'] ?? false),
            description: $validated['description'] ?? null,
            attributes: $validated['attributes'] ?? [],
            schedule: $validated['schedule'] ?? [],
            animal_ids: $validated['animal_ids'] ?? [],
            seoData: $validated['seo'] ?? []
        );
    }

    /**
    * Convert to an array for saving in the database (without relationships)
    */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'category_id' => $this->category_id,
            'availability_type' => $this->availability_type,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'attributes' => $this->attributes,
            'schedule' => $this->schedule,
        ];
    }
}
