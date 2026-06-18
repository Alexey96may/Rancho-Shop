<?php

namespace App\Policies;

use App\Models\Animal;
use App\Models\User;
use App\Enums\UserRole;

class AnimalPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can delete the model (мягкое удаление).
     */
    public function delete(User $user, Animal $animal): bool
    {
        return true; 
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Animal $animal): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Animal $animal): bool
    {
        return false;
    }
}
