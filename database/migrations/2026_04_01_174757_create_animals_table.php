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
        Schema::create('animals', function (Blueprint $table) {

            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('animals') // Указываем явно, что ссылаемся на эту же таблицу
                ->onDelete('set null');

            $table->string('name');
            $table->string('type');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->string('status')->default('home');
            $table->text('bio')->nullable();

            $table->json('features')->nullable(); // Характеристики: ['Жирность' => '4.2%', 'Характер' => 'Тихая']

            $table->timestamps();
            $table->softDeletes(); // В моделе use SoftDeletes;

            // $table->string('audio_path')->nullable(); // Путь к mp3 "приветствию"  // Удалим. Всё будет через Spatie.
            // $table->json('images')->nullable(); // Удалим. Всё будет через Spatie.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animals');
    }
};

// 0. Spatie
//    use Spatie\MediaLibrary\HasMedia;
//    use Spatie\MediaLibrary\InteractsWithMedia;

// public function registerMediaCollections(): void
// {

//     use InteractsWithMedia;

// // Коллекция для фото (с конверсиями под превью)
// $this->addMediaCollection('images')
// ->useFallbackUrl('/images/placeholder-animal.jpg');

//         // Коллекция для аудио (без конверсий, только хранение)
//         $this->addMediaCollection('voice')
//              ->singleFile(); // У животного только один "голос" за раз
//     }

// 1.Чтобы Laravel автоматически превращал JSON из базы в удобный массив/объект, в модели Cow нужно прописать Casts:
// protected $casts = [

//     'features' => 'array', // Или 'object', или AsArrayObject::class
//     'is_active' => 'boolean',

// ];

// 2. Для родства в модели Cow.
//    // Получить мать коровы

// public function parent()
// {

//     return $this->belongsTo(Cow::class, 'parent_id');

// }

// // Получить всех дочерей коровы
// public function children()
// {

//     return $this->hasMany(Cow::class, 'parent_id');

// }

// SoftDeletes: Не забудь в модели Animal прописать protected $dates = ['deleted_at']; (в новых версиях Laravel это делается через каст в методе casts()), чтобы Carbon корректно работал с этой датой.
