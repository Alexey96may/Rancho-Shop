<?php

namespace App\Actions\Admin\Pages;

use App\DTO\PageDataDTO;
use App\Models\Page;
use App\Services\Admin\PageMediaService;

class CreatePageAction
{
    public function __construct(
        private PageMediaService $mediaService,
    ) {}

    public function handle(PageDataDTO $dto): Page
    {
        $page = Page::create($dto->toPageArray());

        if ($dto->content) {
            $this->mediaService->moveTemporaryMedia($dto->content, $page);
        }

        $page->syncSeo($dto->seoData);

        return $page;
    }
}
