<?php

namespace App\Actions\Admin\Units;

use App\Models\Unit;

class CreateUnitAction
{
    public function handle(array $data): Unit
    {
        return Unit::create([
            ...$data,
            'position' => (Unit::max('position') ?? 0) + 1,
        ]);
    }
}
