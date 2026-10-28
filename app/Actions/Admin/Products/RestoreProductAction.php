<?php

namespace App\Actions\Admin\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Gate;

class RestoreProductAction
{
    public function handle(Product $product): void
    {
        Gate::authorize('restore', $product);
        $product->restore();
    }
}
