<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {

            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('animal_id')->nullable()->constrained()->onDelete('set null');

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->integer('old_price')->nullable();

            $table->integer('price'); // Храним в копейках/центах (integer), чтобы не было проблем с float
            $table->string('unit')->default('литр'); // кг, шт, баночка
            $table->integer('stock')->default(0);

            $table->string('availability_type')->default('daily');
            $table->json('schedule')->nullable(); // Чтобы хранить: "Пн, Ср, Пт"

            $table->json('attributes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // $table->json('images')->nullable(); // Удалим. Всё будет через Spatie.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

// Spatie
// public function registerMediaCollections(): void
// {
// $this->addMediaCollection('gallery') // Основные фото товара
// ->useFallbackUrl('/images/no-product.jpg');
// }

// // Чтобы обычные пользователи не видели выключенные товары
// protected static function booted()
// {

//     static::addGlobalScope('active', function (Builder $builder) {
//     // Если это не админ-панель, показываем только активные
//     if (!request()->is('admin/\*')) {
//     $builder->where('is_active', true);
//     }

// });
// }
