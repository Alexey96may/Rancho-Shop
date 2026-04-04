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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Сам код, например 'VESNA2026'

            // Тип скидки: фиксированная (рубли) или процент (%)
            $table->enum('type', ['fixed', 'percent'])->default('fixed');

            $table->unsignedInteger('value'); // Значение (в копейках или целых процентах)

            // Ограничения
            $table->unsignedInteger('min_order_amount')->default(0); // Мин. сумма чека (в копейках)
            $table->unsignedInteger('max_discount')->nullable();     // Макс. скидка (для процентов)

            // Лимиты и сроки
            $table->unsignedInteger('usage_limit')->nullable();      // Общее кол-во активаций
            $table->unsignedInteger('used_count')->default(0);       // Сколько раз уже применили
            $table->timestamp('expires_at')->nullable();             // Дата истечения

            $table->boolean('is_active')->default(true);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
