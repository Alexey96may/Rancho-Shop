<?php

namespace App\Actions\Admin\Pages;

use App\Models\Page;
use RuntimeException;

class DeletePageAction
{
    public function handle(Page $page): void
    {
        if (!$page->isDeletable()) {
            throw new RuntimeException('Эту страницу нельзя удалить, так как она является системной.');
        }

        $page->seo()->delete();
        $page->delete();
    }
}
