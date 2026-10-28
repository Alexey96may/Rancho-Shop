<?php

namespace App\Actions\Admin\Animals;

use App\DTO\AnimalDataDTO;
use App\Models\Animal;
use App\Traits\HandlesAdminMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreateAnimalAction
{
    use HandlesAdminMedia;

    public function handle(AnimalDataDTO $dto, Request $request): Animal
    {
        return DB::transaction(function () use ($dto, $request) {
            $animal = Animal::create($dto->toArray());
            $animal->syncSeo($dto->seoData);

            $this->syncModelMedia($animal, $request);

            return $animal;
        });
    }
}
