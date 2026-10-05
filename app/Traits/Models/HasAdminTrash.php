<?php

namespace App\Traits\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasAdminTrash
{
    /**
     * A local scope for managing the SoftDeletes trash based on role.
     * * @param Builder $query
     * @param Request $request
     * @param array $filters
     * @return Builder
     */
    public function scopeWithTrashControl(Builder $query, Request $request, array &$filters): Builder
    {
        $user = $request->user();

        if ($user && $user->role === UserRole::ADMIN) {
            if (($filters['status'] ?? null) === 'trash') {
                return $query->onlyTrashed();
            }
            
            return $query->withTrashed();
        }

        // If not an admin, forcibly remove the 'trash' status from the filters.
        if (isset($filters['status']) && $filters['status'] === 'trash') {
            unset($filters['status']);
        }

        return $query;
    }
}