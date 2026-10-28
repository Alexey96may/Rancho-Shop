<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOrderAccess
{
    public function handle(Request $request, Closure $next)
    {
        $order = $request->route('order');

        if (!$order instanceof Order) {
            return $next($request);
        }

        $isOwner = $order->user_id && $order->user_id === Auth::id();
        $hasToken = $request->query('token') === $order->payment_token;

        if (!$isOwner && !$hasToken) {
            abort(403);
        }

        return $next($request);
    }
}
