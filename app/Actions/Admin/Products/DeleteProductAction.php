<?php

namespace App\Actions\Admin\Products;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeleteProductAction
{
    public function handle(Product $product): void
    {
        if ($product->trashed()) {
            Gate::authorize('forceDelete', $product);

            DB::transaction(function () use ($product) {
                $product->seo?->delete();
                $product->clearMediaCollection('main');
                $product->clearMediaCollection('gallery');
                $product->variants()->delete();
                $product->animals()->detach();
                $product->forceDelete();
            });

            return;
        }

        Gate::authorize('delete', $product);
        $product->delete();
    }
}
