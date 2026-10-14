<?php

namespace App\Actions\Checkout;

use App\Models\PromoCode;
use Illuminate\Validation\ValidationException;

class ApplyPromoCodeAction
{
    /**
     * @throws ValidationException
     */
    public function handle(string $code, int $subtotal): PromoCode
    {
        $promo = PromoCode::where('code', $code)->first();

        if (!$promo) {
            throw ValidationException::withMessages([
                'promo_code' => 'Промокод не найден.',
            ]);
        }

        if (!$promo->isValid()) {
            throw ValidationException::withMessages([
                'promo_code' => 'Промокод недействителен или истёк.',
            ]);
        }

        if ($subtotal < $promo->min_order_amount) {
            $min = number_format($promo->min_order_amount / 100, 0, '.', ' ');
            throw ValidationException::withMessages([
                'promo_code' => "Минимальная сумма заказа — {$min} ₽.",
            ]);
        }

        return $promo;
    }
}
