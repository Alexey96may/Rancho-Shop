<?php

namespace App\Actions\Admin\ProductVariants;

use App\Models\ProductVariant;

class DeleteProductVariantAction
{
    public function handle(ProductVariant $variant): void
    {
        $variant->delete();
    }
}
