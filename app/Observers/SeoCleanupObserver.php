<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class SeoCleanupObserver
{
    /**
     * Deletes the SEO record when the model is force-deleted.
     * No action is taken during a soft delete—the SEO record remains
     * so it can be restored along with the model.
     */
    public function deleting(Model $model): void
    {
        // If the model uses SoftDeletes and it's not a force-delete, skip it.
        if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
            return;
        }

        $model->seo()->delete();
    }
}
