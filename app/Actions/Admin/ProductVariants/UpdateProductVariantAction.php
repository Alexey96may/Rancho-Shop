<?php

namespace App\Actions\Admin\ProductVariants;

use App\DTO\CatalogDataDTO;
use App\Models\ProductVariant;

class UpdateProductVariantAction
{
    public function handle(ProductVariant $variant, CatalogDataDTO $dto): ProductVariant
    {
        $variant->update($dto->toArray());

        return $variant;
    }
}
