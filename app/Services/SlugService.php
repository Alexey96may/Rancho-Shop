<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugService
{
    /**
     * Generates a unique slug based on the submitted form fields.
     *
     * @param  string|null  $slug  Явно переданный slug (если есть)
     * @param  string  $fallbackName  Имя, из которого генерировать slug, если $slug пуст
     * @param  class-string<Model>|null  $modelClass  Модель для проверки уникальности
     * @param  int|null  $ignoreId  ID записи, которую нужно игнорировать (при update)
     */
    public static function prepare(
        ?string $slug,
        string $fallbackName,
        ?string $modelClass = null,
        ?int $ignoreId = null,
    ): string {
        $base = empty($slug) ? Str::slug($fallbackName) : Str::slug($slug);

        if (!$modelClass) {
            return $base;
        }

        $query = method_exists($modelClass, 'withTrashed')
            ? $modelClass::withTrashed()
            : $modelClass::query();

        $slug = $base;
        $i = 2;

        while (
            (clone $query)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
