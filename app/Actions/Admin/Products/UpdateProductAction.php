<?php

namespace App\Actions\Admin\Products;

use App\DTO\ProductDataDTO;
use App\Models\Product;
use App\Traits\HandlesAdminMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpdateProductAction
{
    use HandlesAdminMedia;

    public function handle(Product $product, ProductDataDTO $dto, Request $request): Product
    {
        DB::transaction(function () use ($product, $dto, $request) {
            $product->update($dto->toArray());

            $product->animals()->sync($dto->animal_ids);
            $product->syncSeo($dto->seoData);

            $this->syncModelMedia($product, $request);
        });

        return $product;
    }
}
