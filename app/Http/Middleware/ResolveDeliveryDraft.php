<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveDeliveryDraft
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $draft = null;

        // 👤 AUTH USER
        if ($request->user()) {
            $draft = $request->user()
                ->deliveryAddresses()
                ->latest()
                ->first();
        }

        // 👤 GUEST
        if (!$draft) {
            $draft = session('delivery_draft');
        }

        // 🧠 attach to request
        $request->attributes->set('delivery_draft', $draft);

        return $next($request);
    }
}
