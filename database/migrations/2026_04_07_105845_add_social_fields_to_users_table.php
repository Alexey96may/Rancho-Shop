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
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique(); // Google
            $table->string('vkontakte_id')->nullable()->unique(); // VK
            $table->text('avatar_url')->nullable(); // Social avatar link
            $table->string('password')->nullable()->change(); // nullable password
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'vkontakte_id', 'avatar_url']);
        });
    }
};