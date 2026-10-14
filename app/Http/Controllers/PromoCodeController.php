<?php

namespace App\Http\Controllers;

use App\Actions\Checkout\ApplyPromoCodeAction;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function check(Request $request, ApplyPromoCodeAction $applyPromo)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'subtotal' => ['required', 'integer', 'min:0'],
        ]);

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
