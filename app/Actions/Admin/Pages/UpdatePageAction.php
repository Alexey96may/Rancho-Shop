<?php

namespace App\Actions\Admin\Pages;

use App\DTO\PageDataDTO;
use App\Models\Page;
use App\Services\Admin\PageMediaService;

class UpdatePageAction
{
    public function __construct(
        private PageMediaService $mediaService,
    ) {}

    public function handle(Page $page, PageDataDTO $dto): Page
    {
        if ($dto->content !== null) {
            $this->mediaService->sanitizeMedia($dto->content, $page);
        }

        $page->update($dto->toPageArray());
        $page->syncSeo($dto->seoData);

        return $page;
    }
}
