<?php

namespace App\Actions\Admin\Products;

use App\DTO\ProductDataDTO;
use App\Models\Product;
use App\Traits\HandlesAdminMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreateProductAction
{
    use HandlesAdminMedia;

    public function handle(ProductDataDTO $dto, Request $request): Product
    {
        return DB::transaction(function () use ($dto, $request) {
            $product = Product::create($dto->toArray());

            $product->animals()->sync($dto->animal_ids);
            $product->syncSeo($dto->seoData);

            $this->syncModelMedia($product, $request);

            return $product;
        });
    }
}
