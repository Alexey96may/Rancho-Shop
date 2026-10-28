<?php

namespace App\Services\Admin;

use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;

class PageMediaService
{
    public function sanitizeMedia(string $content, Page $page): void
    {
        foreach ($page->getMedia('content_images') as $media) {
            if (!str_contains($content, $media->getUrl())) {
                $media->delete();
            }
        }
    }

    public function moveTemporaryMedia(string $content, Page $page): void
    {
        /** @var User|HasMedia $user */
        $user = Auth::user();
        $temporaryMedia = $user->getMedia('tmp');

        foreach ($temporaryMedia as $media) {
            if (str_contains($content, $media->getUrl())) {
                $media->move($page, 'content_images');
            }
        }

        $keepIds = $user->getMedia('tmp')
            ->where('created_at', '>', now()->subDay())
            ->pluck('id')
            ->all();

        $user->clearMediaCollectionExcept('tmp', $keepIds);
    }
}
