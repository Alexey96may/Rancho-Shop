# Общие Задачи:

- [] «Оплата при получении».
- [] Мягкое удаление (Коровы и товары, заказы не удаляем вообще).
- [] сквозные UUID или короткие номера заказов вместо обычных ID (1, 2, 3...), чтобы конкуренты не могли понять по номеру заказа, сколько у тебя продаж.
- [] Pinia (Для корзины и настроек).
- [] Система скидок.
- [] Система промокодов.
- [] Mongo DB.
- [] Filament + Livewire + AlpineJS.
- [] API для андроид приложения.

## Страницы:

- [] Главная:
- [] Магазин:
- [] О нас:
- [] Контакты:
- [] 404:
- [] Термс:
- [] Корзина:
- [] Чекаут:

- [] Продукт (динамич.):
- [] Корова (динамич.):

- [] Отзывы с авторизацией через гугл, вк:

- [] Админка Главная:
- [] Админка Продукты:
- [] Админка Заказы:
- [] Админка Коровы:
- [] Админка Отзывы:
- [] Админка Настройки:

## Сео:

- [] SSR.
- [] Мета-теги, включая Open Graph & Twitter (под каждый контроллер).
- [] Sitemaps.
- [] Robots (без генерации через админку).
- [] Разные логотипы, в зависимости от типа страниц.
- [] Канонические ссылки.
- [] Ридеры: Aria-label, aria-busy, aria-hidden, aria-selected, aria-current, aria-disabled, aria-checked, aria-invalid, aria-describedby, aria-haspopup, aria-expanded, aria-live, aria-labelledby, role...

## Библиотеки

- [] Spatie MediaLibrary
- []
- []
- []
- []
- []
- []

Чтобы не передавать лишнее обычным пользователям, используй API Resources в Laravel.
CowResource — отдает только id, name, status.
AdminCowResource — отдает всё, включая created_at и is_active.

Добавить Родство коров!

## Models & Migrations

- [] Cow: name, slug, status (enum: 'pasture', 'rest', 'home'), bio, audio_path, image_path (лучше media галерея).
- [] Product: name, price, unit (литр/кг), stock, cow_id (nullable, если продукт общий), slug (возможно), image_path (лучше media галерея).
- [] Order:
- [] Order Item:
- [] Setting:
- [] Review:
- [] Comment:
- [] Cart (не будет таблички, так как нет авторизации). Сохранять товары в localStorage и Синхронизировать цены (если ты сменил цену в админке, корзина должна это "почувствовать").

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

    //$table->string('audio_path')->nullable(); // Путь к mp3 "приветствию"  // Удалим. Всё будет через Spatie.

    //$table->json('images')->nullable(); // Удалим. Всё будет через Spatie.

    $table->json('features')->nullable(); // Характеристики: ['Жирность' => '4.2%', 'Характер' => 'Тихая']

    $table->timestamps();
    $table->softDeletes(); //В моделе use SoftDeletes;

});

0. Spatie
   use Spatie\MediaLibrary\HasMedia;
   use Spatie\MediaLibrary\InteractsWithMedia;

public function registerMediaCollections(): void
{

    use InteractsWithMedia;

// Коллекция для фото (с конверсиями под превью)
$this->addMediaCollection('images')
->useFallbackUrl('/images/placeholder-animal.jpg');

        // Коллекция для аудио (без конверсий, только хранение)
        $this->addMediaCollection('voice')
             ->singleFile(); // У животного только один "голос" за раз
    }

1.Чтобы Laravel автоматически превращал JSON из базы в удобный массив/объект, в модели Cow нужно прописать Casts:
protected $casts = [

    'features' => 'array', // Или 'object', или AsArrayObject::class
    'is_active' => 'boolean',

];

2. Для родства в модели Cow.
   // Получить мать коровы

public function parent()
{

    return $this->belongsTo(Cow::class, 'parent_id');

}

// Получить всех дочерей коровы
public function children()
{

    return $this->hasMany(Cow::class, 'parent_id');

}

SoftDeletes: Не забудь в модели Animal прописать protected $dates = ['deleted_at']; (в новых версиях Laravel это делается через каст в методе casts()), чтобы Carbon корректно работал с этой датой.

Schema::create('products', function (Blueprint $table) {

    $table->id();
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

    // $table->json('images')->nullable(); // Удалим. Всё будет через Spatie.

    $table->timestamps();
    $table->softDeletes();

});

//Spatie
public function registerMediaCollections(): void
{
$this->addMediaCollection('gallery') // Основные фото товара
->useFallbackUrl('/images/no-product.jpg');
}

// Чтобы обычные пользователи не видели выключенные товары
protected static function booted()
{

    static::addGlobalScope('active', function (Builder $builder) {
    // Если это не админ-панель, показываем только активные
    if (!request()->is('admin/\*')) {
    $builder->where('is_active', true);
    }

});
}

// Orders
Schema::create('orders', function (Blueprint $table) {

    $table->id();

    $table->foreignId('promo_code_id')
      ->nullable()
      ->constrained('promo_codes') // Явно указываем таблицу
      ->onDelete('set null');

    // Информация о клиенте
    $table->string('customer_name');
    $table->string('customer_phone');
    $table->text('delivery_address')->nullable();
    $table->text('customer_comment')->nullable(); // Комментарий клиента к заказу

    $table->unsignedInteger('discount_total')->default(0);

    // Финансовые итоги
    $table->integer('total_price'); // Сумма всего заказа в копейках
    $table->integer('delivery_price')->default(0); // Стоимость доставки

    $table->enum('status', ['new', 'confirmed', 'delivering', 'completed', 'cancelled'])
          ->default('new');

    $table->text('admin_note')->nullable(); // Комментарий админа к заказу
    $table->timestamps();

});

// Order Items
Schema::create('order_items', function (Blueprint $table) {

    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');

    // Связь с товаром (nullable, если товар удалят из магазина позже)
    $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

    // Снапшот данных (Дублируем инфо из таблицы products)
    $table->string('product_name'); // Например: "Молоко от Белки"
    $table->integer('price_at_purchase'); // Цена, которая была В МОМЕНТ заказа
    $table->integer('base_price_at_purchase');
    $table->integer('quantity'); // Сколько штук/литров взял

    $table->timestamps();

});

Schema::create('comments', function (Blueprint $table) {

    $table->id();
    $table->string('user_name');
    $table->text('content');
    $table->unsignedTinyInteger('rating')->nullable()->default(5);
    $table->boolean('is_published')->default(false);

    $table->nullableMorphs('commentable');
    $table->timestamps();

});

Schema::create('settings', function (Blueprint $table) {

    $table->id();
    $table->string('key')->unique(); // 'phone', 'announcement_work_days'
    $table->text('value')->nullable();
    $table->string('type')->default('string'); // 'integer', 'boolean', 'string', 'json'
    $table->timestamps();

});

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

app/Traits/HasSeo.php Трейт для Моделей (Backend)

namespace App\Traits;

use App\Models\Seo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo {
public function seo(): MorphOne {
return $this->morphOne(Seo::class, 'seoable');
}
}
Теперь в модели Product.php просто пишем use HasSeo;, и в админке сможем сохранять мета-теги.

### Универсальный контроллер для комментов:

Route::post('/comments/{type}/{id}', [CommentController::class, 'store'])
->name('comments.store');

    namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Comment;

class CommentController extends Controller
{

    public function store(Request $request, string $type, int $id)
    {

        // 1. Валидация входящих данных
        $validated = $request->validate([
            'user_name' => 'required|string|max:255',
            'content' => 'required|string|min:5',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        // 2. Превращаем 'cow' в 'App\Models\Cow'
        // Для этого в Laravel есть крутой хелпер Relation::getMorphedModel
        $modelClass = Relation::getMorphedModel($type);

        if (!$modelClass) {
            abort(400, "Неизвестный тип объекта для комментирования.");
        }

        // 3. Находим саму корову или продукт
        $model = $modelClass::findOrFail($id);

        // 4. Создаем комментарий через связь!
        $comment = $model->comments()->create([
            'user_name' => $validated['user_name'],
            'content' => $validated['content'],
            'rating' => $validated['rating'] ?? null,
            'is_published' => false, // отправляем на модерацию
        ]);

        // 5. Возвращаемся назад через Inertia
        return back()->with('success', 'Спасибо! Ваш отзыв отправлен на модерацию.');
    }

}

Пишем бут
app/Providers/AppServiceProvider.php

public function boot(): void
{

    Relation::enforceMorphMap([

        'cow' => \App\Models\Cow::class,
        'product' => \App\Models\Product::class,
        'site' => \App\Models\Site::class, // Для отзывов о сайте
    ]);

}

подсчет прайса
$total_price = (сумма(price_at_purchase \* quantity)) + delivery_price - discount_total
