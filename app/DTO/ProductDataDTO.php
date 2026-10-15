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
        public bool $is_commentable,
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
            is_commentable: (bool) ($validated['is_commentable'] ?? true),
            is_active: (bool) ($validated['is_active'] ?? false),
            description: $validated['description'] ?? null,
            attributes: array_values(array_filter(
                $validated['attributes'] ?? [],
                fn ($a) => !empty($a['label'])
            )),
            schedule: self::normalizeSchedule($validated['schedule'] ?? []),
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
            'is_commentable' => $this->is_commentable,
            'description' => $this->description,
            'attributes' => $this->attributes,
            'schedule' => $this->schedule,
        ];
    }

    private static function normalizeSchedule(array $schedule): array
    {
        return [
            'next_delivery' => $schedule['next_delivery'] ?? null,
            'interval_days' => isset($schedule['interval_days']) ? (int) $schedule['interval_days'] : null,
            'note' => $schedule['note'] ?? null,
        ];
    }
}
