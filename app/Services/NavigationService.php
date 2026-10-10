<?php

namespace App\Services;

use App\Models\Page;

class NavigationService
{
    /**
     * Возвращает объединённый список пунктов меню:
     * статические разделы + CMS-страницы, помеченные show_in_menu.
     */
    public static function items(): array
    {
        $items = [];

        // CMS
        $pages = Page::query()->inMenu()->get(['id', 'title', 'slug']);

        foreach ($pages as $page) {
            $items[] = [
                'name' => $page->title,
                'href' => route('pages.show', $page->slug),
                'pattern' => "pages.show:{$page->slug}",
            ];
        }

        return $items;
    }
}
