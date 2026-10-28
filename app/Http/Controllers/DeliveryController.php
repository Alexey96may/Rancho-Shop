<?php

namespace App\Http\Controllers;

use App\Actions\Delivery\SaveDeliveryDraftAction;
use App\Http\Requests\StoreDeliveryRequest;

class DeliveryController extends Controller
{
    public function store(StoreDeliveryRequest $request, SaveDeliveryDraftAction $action)
    {
        $action->handle($request->validated(), $request->user());

        return back()->with('success', 'Ваше местоположение сохранено!');
    }
}
