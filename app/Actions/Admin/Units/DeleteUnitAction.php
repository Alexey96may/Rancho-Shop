<?php

namespace App\Actions\Admin\Units;

use App\Models\Unit;
use RuntimeException;

class DeleteUnitAction
{
    public function handle(Unit $unit): void
    {
        if ($unit->productVariants()->exists()) {
            throw new RuntimeException("Нельзя удалить «{$unit->name}»: единица используется в товарах");
        }

        $unit->delete();
    }
}
