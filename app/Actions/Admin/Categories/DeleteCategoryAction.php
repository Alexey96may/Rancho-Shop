<?php

namespace App\Actions\Admin\Categories;

use App\Models\Category;

class DeleteCategoryAction
{
    public function handle(Category $category): bool
    {
        if ($category->products()->exists() || $category->animals()->exists()) {
            return false;
        }

        $category->delete();

        return true;
    }
}
