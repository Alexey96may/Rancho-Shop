<?php

namespace App\Actions\Admin\Catalog;

use App\Models\ProductVariant;

class DeleteProductVariantAction
{
    public function handle(ProductVariant $variant): void
    {
        $variant->delete();
    }
}
