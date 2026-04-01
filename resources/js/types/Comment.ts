// Поскольку ты планируешь использовать MorphMap (где вместо полных путей к классам будут короткие имена 'cow', 'product'),
// в TypeScript ты всё указал верно.
//  Главное — не забудь зарегистрировать этот маппинг в AppServiceProvider на бэкенде.

export interface Comment {
    id: number;
    user_name: string;
    content: string;
    rating: number | null; // От 1 до 5

    // Полиморфные поля (из $table->nullableMorphs)
    commentable_id: number | null;
    commentable_type: "cow" | "product" | "site"; // Наши ключи из MorphMap

    // Дата для отображения (например, "2 часа назад")
    created_at: string;
}

export interface AdminComment extends Comment {
    is_published: boolean; // Чтобы ты мог нажать "Опубликовать"
    updated_at: string;

    // В админке нам часто нужно знать, к какому именно объекту привязан коммент
    // Например, чтобы вывести: "Отзыв к товару: Молоко"
    commentable?: {
        name: string;
        slug: string;
        type: "cow" | "product" | "site";
    };
}
