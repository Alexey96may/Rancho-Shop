<?php

namespace App\Actions\Comments;

use App\Enums\CommentableType;
use Illuminate\Validation\ValidationException;

class EnsureCommentableAction
{
    /**
     * Проверяет, что к материалу можно оставить комментарий.
     * Бросает ValidationException, если нельзя.
     *
     * @throws ValidationException
     */
    public function handle(?CommentableType $type, ?int $id): void
    {
        // Отзыв о сайте — commentable_type и id = null, всё ок
        if ($type === null && $id === null) {
            return;
        }

        // Если один есть, а другого нет — невалидное состояние
        if ($type === null || $id === null) {
            throw ValidationException::withMessages([
                'content' => 'Некорректный материал для комментария.',
            ]);
        }

        $modelClass = $type->modelClass();
        $commentable = $modelClass::find($id);

        if (!$commentable) {
            throw ValidationException::withMessages([
                'content' => 'Материал не найден.',
            ]);
        }

        if (!$commentable->is_commentable) {
            throw ValidationException::withMessages([
                'content' => 'Комментарии к этому материалу отключены.',
            ]);
        }

        if (method_exists($commentable, 'is_active') && !$commentable->is_active) {
            throw ValidationException::withMessages([
                'content' => 'Материал скрыт.',
            ]);
        }
    }
}
