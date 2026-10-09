<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Страница оплаты/перенаправления на эквайринг
     */
    public function checkout(Order $order, PaymentGatewayInterface $paymentGateway)
    {

        if ($order->user_id && $order->user_id !== Auth::id()) {
            abort(403);
        }

        // Если уже оплачен — уходим в заказы
        if ($order->payment_status === 'paid') {
            return Auth::check()
                ? redirect()->route('profile.orders.index')
                : redirect()->route('home')->with('success', 'Заказ уже оплачен');
        }

        $paymentData = $paymentGateway->getPaymentData($order);

        return inertia('Checkout/Payment', [
            'order' => $order,
            'payment' => $paymentData,
        ]);
    }

    /**
     * Webhook/Callback от PayMaster
     */
    public function callback(Request $request, PaymentGatewayInterface $paymentGateway)
    {
        if (!$paymentGateway->validateCallback($request->all())) {
            Log::warning('PayMaster Callback: Неверная подпись', $request->all());

            return response('Invalid signature', 400);
        }

        $orderId = $request->input('LMI_PAYMENT_NO');
        $order = Order::find($orderId);

        if (!$order) {
            return response('Order not found', 404);
        }

        if ($order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'status' => OrderStatus::CONFIRMED,
                'payment_id' => $request->input('LMI_SYS_PAYMENT_ID'),
            ]);
        }

        return response('OK', 200);
    }

    /**
     * Тестовый роут для мгновенной симуляции оплаты (для PAYMENT_DRIVER="fake")
     */
    public function fakeProcess(Order $order)
    {
        if (config('services.payment.driver') !== 'fake') {
            abort(404);
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => OrderStatus::CONFIRMED,
            'payment_id' => 'fake_trx_' . time(),
        ]);

        return redirect()->route('profile.orders.index')
            ->with('success', 'Заказ успешно оплачен (Fake Driver)');
    }
}
