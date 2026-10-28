<?php

namespace App\Http\Controllers;

use App\Actions\Checkout\ApplyPromoCodeAction;
use App\Http\Requests\ApplyPromoCodeRequest;

class PromoCodeController extends Controller
{
    /**
     * ВНИМАНИЕ: subtotal приходит с клиента и НЕ является доверенным.
     * Используется только для предпросмотра скидки в UI.
     * При оформлении заказа сумму и скидку пересчитывать на сервере.
     */
    public function check(ApplyPromoCodeRequest $request, ApplyPromoCodeAction $applyPromo)
    {
        $data = $request->validated();

        $promo = $applyPromo->handle($data['code'], $data['subtotal']);

        session(['promo_code_id' => $promo->id]);

        return back()->with('promo', [
            'id' => $promo->id,
            'code' => $promo->code,
            'type' => $promo->type->value,
            'value' => $promo->value,
            'discount' => $promo->calculateDiscount($data['subtotal']),
            'description' => $promo->description,
        ]);
    }

    public function remove()
    {
        session()->forget('promo_code_id');

        return back()->with('promo', null);
    }
}
