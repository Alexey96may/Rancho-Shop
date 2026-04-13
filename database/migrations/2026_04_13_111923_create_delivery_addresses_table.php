<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_addresses', function (Blueprint $table) {
            // owner (nullable = guest drafts or future guest checkout)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // main data
            $table->string('address');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);

            // metadata
            $table->boolean('is_default')->default(false);

            $table->string('label')->nullable(); // "Home", "Office", etc.

            $table->timestamps();

            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_addresses');
    }
};
