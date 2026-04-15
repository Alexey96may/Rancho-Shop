<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        //Route::middleware(['role:admin,moderator'])->group(...)
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!in_array($request->user()->role->value, $roles)) {
            abort(403);
        }

        if (!$request->user()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        return $next($request);
    }
}
