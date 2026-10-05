<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HandlesAdminMedia
{
    /**
    * A universal media synchronization method for any model (Product, Animal, etc.)
    */
    protected function syncModelMedia(Model $model, Request $request): void
    {
        // 1. Manually remove media by ID (if submitted from a form, as in ProductController)
        if ($request->has('remove_media') && is_array($request->remove_media)) {
            Media::whereIn('id', $request->remove_media)
                ->where('model_type', get_class($model))
                ->where('model_id', $model->id)
                ->delete();
        }

        // 2. Processing the Main Photo (avatar for animals, main_photo for products)
        $mainPhotoKey = $request->has('avatar') ? 'avatar' : ($request->has('main_photo') ? 'main_photo' : null);
        
        if ($mainPhotoKey) {
            // Define the Spatie collection name for a specific model
            $collectionName = ($mainPhotoKey === 'avatar') ? 'avatars' : 'main';
            $file = $request->file($mainPhotoKey);

            if (is_array($file)) {
                $file = head($file);
            }

            if ($file && $file->isValid()) {
                $model->clearMediaCollection($collectionName);
                $model->addMedia($file)->toMediaCollection($collectionName);
            } elseif ($request->exists($mainPhotoKey) && empty($request->input($mainPhotoKey)) && empty($request->file($mainPhotoKey))) {
                $model->clearMediaCollection($collectionName);
            }
        }

        // 3. Gallery Processing (the same for everyone)
        if ($request->has('gallery')) {
            $currentMediaIds = collect($request->input('gallery'))
                ->filter(fn($item) => is_array($item) && isset($item['id']))
                ->pluck('id')
                ->toArray();

            $model->getMedia('gallery')
                ->reject(fn($media) => in_array($media->id, $currentMediaIds))
                ->each(fn($media) => $media->delete());

            if ($request->hasFile('gallery')) {
                $files = $request->file('gallery');
                $files = is_array($files) ? $files : [$files];

                foreach ($files as $file) {
                    if ($file->isValid()) {
                        $model->addMedia($file)->toMediaCollection('gallery');
                    }
                }
            }
        } elseif ($request->exists('gallery')) {
            $model->clearMediaCollection('gallery');
        }

        // 4. Audio Processing (specific to Animal, check for the presence of the collection using the Spatie method)
        if (method_exists($model, 'getMediaModelInfo') || isset($model->mediaConvetions) || method_exists($model, 'registerMediaCollections')) {
            if ($request->hasFile('voice')) {
                $model->addMediaFromRequest('voice')->toMediaCollection('voice');
            } elseif ($request->has('voice') && is_null($request->input('voice'))) {
                $model->clearMediaCollection('voice');
            }
        }
    }
}