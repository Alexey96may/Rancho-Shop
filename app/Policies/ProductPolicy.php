<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;
use App\Enums\UserRole;

class ProductPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->role === UserRole::MODERATOR;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return false;
    }
}
