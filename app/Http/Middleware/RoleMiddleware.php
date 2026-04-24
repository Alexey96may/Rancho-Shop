<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $allowedRoles = array_map(
            fn ($role) => UserRole::from($role),
            $roles
        );

        if (!in_array($user->role, $allowedRoles)) {
            abort(403);
        }
        
        return $next($request);
    }
}
