<?php

namespace App\Actions\Admin\Animals;

use App\DTO\AnimalDataDTO;
use App\Models\Animal;
use App\Traits\HandlesAdminMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpdateAnimalAction
{
    use HandlesAdminMedia;

    public function handle(Animal $animal, AnimalDataDTO $dto, Request $request): Animal
    {
        DB::transaction(function () use ($animal, $dto, $request) {
            $animal->update($dto->toArray());
            $animal->syncSeo($dto->seoData);

            $this->syncModelMedia($animal, $request);
        });

        return $animal;
    }
}
