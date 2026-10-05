<?php

namespace App\DTO;
use App\Services\SanitizeService;

class LandingBlockDataDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $title,
        public ?string $subtitle,
        public bool $is_visible,
        public array $content
    ) {}

    public static function fromRequest(array $validated): self
    {
        $sanitizedContent = collect($validated['content'] ?? [])->map(function ($item) {
            return [
                'title' => SanitizeService::cleanHtml($item['title'] ?? null),
                'desc'  => SanitizeService::cleanHtml($item['desc'] ?? ''),
                'icon'  => isset($item['icon']) ? strip_tags($item['icon']) : null,
                'step'  => isset($item['step']) ? (int) $item['step'] : null,
            ];
        })->toArray();

        return new self(
            title: SanitizeService::cleanHtml($validated['title']),
            subtitle: isset($validated['subtitle']) ? SanitizeService::cleanHtml($validated['subtitle']) : null,
            is_visible: (bool) ($validated['is_visible'] ?? false),
            content: $sanitizedContent
        );
    }

    public function toArray(): array
    {
        return [
            'title'      => $this->title,
            'subtitle'   => $this->subtitle,
            'is_visible' => $this->is_visible,
            'content'    => $this->content,
        ];
    }
}
