<?php

namespace App\Actions\Admin\Animals;

use App\Models\Animal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeleteAnimalAction
{
    public function handle(Animal $animal): void
    {
        if ($animal->trashed()) {
            Gate::authorize('forceDelete', $animal);

            DB::transaction(function () use ($animal) {
                $animal->seo?->delete();
                $animal->media()->delete();
                $animal->forceDelete();
            });

            return;
        }

        Gate::authorize('delete', $animal);
        $animal->delete();
    }
}
