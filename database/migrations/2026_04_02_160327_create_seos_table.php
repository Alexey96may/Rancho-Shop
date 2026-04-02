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
        Schema::create('seos', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable'); // Создаст seoable_id и seoable_type
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('keywords')->nullable();
            $table->jsonb('og_data')->nullable(); // Для соцсетей (Open Graph)
            $table->boolean('is_noindex')->default(false); // Чтобы скрывать технические страницы
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seos');
    }
};


// app/Traits/HasSeo.php Трейт для Моделей (Backend)

// namespace App\Traits;

// use App\Models\Seo;
// use Illuminate\Database\Eloquent\Relations\MorphOne;

// trait HasSeo {
// public function seo(): MorphOne {
// return $this->morphOne(Seo::class, 'seoable');
// }
// }
// Теперь в модели Product.php просто пишем use HasSeo;, и в админке сможем сохранять мета-теги.