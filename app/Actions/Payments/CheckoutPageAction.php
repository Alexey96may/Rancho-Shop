<?php

namespace App\Actions\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class CheckoutPageAction
{
    public function handle(Order $order, ?string $token): Order
    {
        $isOwner = $order->user_id && $order->user_id === Auth::id();
        $hasToken = $token === $order->payment_token;

        if (!$isOwner && !$hasToken) {
            abort(403);
        }

        return $order;
    }
}
