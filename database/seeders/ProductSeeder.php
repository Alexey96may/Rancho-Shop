<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedManualProducts();

        Product::factory()->count(50)->create()->each(function ($product) {
            $this->addSeedImage($product);
        });
    }

    private function seedManualProducts(): void
    {
        $zorka = Animal::where('slug', 'zorka')->first();
        $milkCat = Category::where('slug', 'moloko')->first();
        $l = Unit::where('slug', 'l')->first();

        $milk = Product::create([
            'category_id' => $milkCat?->id,
            'animal_id' => $zorka?->id,
            'name' => 'Молоко коровье цельное',
            'slug' => 'moloko-korovye-tselnoe',
            'description' => 'Парное молоко от нашей Зорьки.',
            'availability_type' => 'daily',
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $milk->id,
            'name' => '1 литр',
            'price' => 12000,
            'stock' => 50,
            'unit_id' => $l?->id,
        ]);

        $this->addSeedImage($milk);
    }

    private function addSeedImage(Product $product): void
    {
        $imagePath = public_path('images/seeds/product-placeholder.png');
        if (File::exists($imagePath)) {
            $product->addMedia($imagePath)
                ->preservingOriginal()
                ->toMediaCollection('gallery');
        }
    }
}
