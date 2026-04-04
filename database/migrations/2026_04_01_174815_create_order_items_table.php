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
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            // Снапшот данных (Дублируем инфо из таблицы products)
            $table->string('product_name'); // Например: "Молоко от Белки"
            $table->integer('price_at_purchase'); // Цена, которая была В МОМЕНТ заказа
            $table->integer('base_price_at_purchase');
            $table->integer('quantity'); // Сколько штук/литров взял

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
