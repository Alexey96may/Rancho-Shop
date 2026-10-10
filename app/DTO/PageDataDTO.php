<?php

namespace App\DTO;

use App\Services\SanitizeService;
use App\Services\SlugService;

class PageDataDTO
{
    public function __construct(
        public string $title,
        public ?string $content,
        public string $type,
        public bool $is_active,
        public string $template,
        public string $slug,
        public bool $show_in_menu,
        public int $menu_order,
        public array $seoData,
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
            show_in_menu: (bool) ($validated['show_in_menu'] ?? false),
            menu_order: (int) ($validated['menu_order'] ?? 0),
            seoData: $validated['seo'] ?? [],
        );
    }

    public function toPageArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'template' => $this->template,
            'slug' => $this->slug,
            'show_in_menu' => $this->show_in_menu,
            'menu_order' => $this->menu_order,
        ];
    }
}
