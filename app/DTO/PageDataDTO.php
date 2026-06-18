<?php

namespace App\DTO;

use App\Services\SanitizeService;
use App\Services\SlugService;

class PageDataDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $title,
        public ?string $content,
        public string $type,
        public bool $is_active,
        public string $template,
        public string $slug,
        public array $seoData
    ) {}

    public static function fromRequest(array $validated): self
    {
        $content = $validated['content'] ?? null;
        if (!empty($content)) {
            $content = SanitizeService::cleanHtml($content);
        }

        return new self(
            title: $validated['title'],
            content: $content,
            type: $validated['type'],
            is_active: (bool) ($validated['is_active'] ?? false),
            template: $validated['template'],
            slug: SlugService::prepare($validated['slug'] ?? null, $validated['title']),
            seoData: $validated['seo'] ?? []
        );
    }

    /**
    * Returns an array for the pages table only
    */
    public function toPageArray(): array
    {
        return [
            'title'     => $this->title,
            'content'   => $this->content,
            'type'      => $this->type,
            'is_active' => $this->is_active,
            'template'  => $this->template,
            'slug'      => $this->slug,
        ];
    }
}
