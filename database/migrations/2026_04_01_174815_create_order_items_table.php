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
        Schema::create('order_items', function (Blueprint $table) {

            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();

            // Снапшот данных (Дублируем инфо из таблицы products)
            $table->string('product_name'); // Например: "Молоко от Белки"
            $table->integer('unit_price'); // Цена, которая была В МОМЕНТ заказа
            $table->integer('old_unit_price')->nullable();
            $table->integer('quantity'); // Сколько штук/литров взял

            // snapshot unit 
            $table->string('unit_name')->nullable();
            $table->string('unit_code')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
