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
        Schema::create('orders', function (Blueprint $table) {

            $table->id();

            $table->foreignId('promo_code_id')
                ->nullable()
                ->constrained('promo_codes')
                ->onDelete('set null');

            // Client Information
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('delivery_address')->nullable();
            $table->text('customer_comment')->nullable(); // Client's comment on the order

            $table->unsignedInteger('discount_total')->default(0);

            // Financial results
            $table->integer('total_price');
            $table->integer('delivery_price')->default(0);

            $table->enum('status', ['new', 'confirmed', 'delivering', 'completed', 'cancelled'])
                ->default('new');

            $table->text('admin_note')->nullable(); // Admin's comment on the order
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
