<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * @property int $total_revenue Revenue in cents (e.g., 1000 = 10.00)
     */
    public function up(): void
    {
        Schema::create('daily_sales_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique()->index();

            $table->bigInteger('total_revenue')->default(0)->comment('Revenue in cents');
            $table->integer('orders_count')->default(0);
            $table->integer('items_count')->default(0)->comment('Number of sold units');
            $table->bigInteger('avg_order_value')->default(0)->comment('Average Check');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_sales_stats');
    }
};
