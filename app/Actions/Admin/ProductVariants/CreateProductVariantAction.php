<?php

namespace App\Actions\Admin\ProductVariants;

use App\DTO\CatalogDataDTO;
use App\Models\ProductVariant;

class CreateProductVariantAction
{
    public function handle(CatalogDataDTO $dto): ProductVariant
    {
        return ProductVariant::create($dto->toArray());
    }
}
