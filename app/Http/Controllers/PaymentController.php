<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Страница оплаты / редирект на эквайринг.
     */
    public function checkout(Order $order, PaymentGatewayInterface $paymentGateway)
    {
        if ($order->user_id && $order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->payment_status === 'paid') {
            return Auth::check()
                ? redirect()->route('profile.orders.index')
                : redirect()->route('home')->with('success', 'Заказ уже оплачен');
        }

        $paymentData = $paymentGateway->getPaymentData($order);

        // Ошибка инициализации платежа (например, Тинькофф вернул Success=false)
        if (($paymentData['type'] ?? null) === 'error') {
            Log::warning('Payment init failed', [
                'order_id' => $order->id,
                'error' => $paymentData['error'] ?? 'unknown',
            ]);

            return redirect()
                ->route('checkout.index')
                ->with('error', $paymentData['error'] ?? 'Не удалось создать платёж');
        }

        return inertia('Checkout/Payment', [
            'order' => $order,
            'payment' => $paymentData,
            'seo' => $this->seo('Оплата покупки', robots: 'noindex, nofollow'),
        ]);
    }

    /**
     * Страница "спасибо" после успешной оплаты (SuccessURL).
     */
    public function success(Order $order)
    {
        // Простейшая защита: пользователь видит только свой заказ
        if ($order->user_id && $order->user_id !== Auth::id()) {
            abort(403);
        }

        return inertia('Checkout/Success', [
            'order' => $order->load('items'),
            'seo' => $this->seo('Заказ оформлен', robots: 'noindex, nofollow'),
        ]);
    }

    /**
     * Webhook от PayMaster.
     */
    public function callbackPaymaster(Request $request, PaymentGatewayInterface $paymentGateway)
    {
        if (!$paymentGateway->validateCallback($request->all())) {
            Log::warning('PayMaster callback: invalid signature', $request->all());

            return response('Invalid signature', 400);
        }

        $order = Order::find($request->input('LMI_PAYMENT_NO'));

        if (!$order) {
            return response('Order not found', 404);
        }

        $this->markAsPaid($order, (string) $request->input('LMI_SYS_PAYMENT_ID'));

        return response('OK', 200);
    }

    /**
     * Webhook от Тинькофф.
     */
    public function callbackTinkoff(Request $request, PaymentGatewayInterface $paymentGateway)
    {
        if (!$paymentGateway->validateCallback($request->all())) {
            Log::warning('Tinkoff callback: invalid token', $request->all());

            return response('Invalid token', 400);
        }

        $order = Order::find($request->input('OrderId'));

        if (!$order) {
            return response('Order not found', 404);
        }

        // 🔒 Сверяем сумму: Тинькофф присылает в копейках
        if ((int) $request->input('Amount') !== (int) $order->total_price) {
            Log::warning('Tinkoff callback: amount mismatch', [
                'order_id' => $order->id,
                'expected' => $order->total_price,
                'got' => $request->input('Amount'),
            ]);

            return response('Amount mismatch', 400);
        }

        // Обрабатываем только успех
        if ($request->input('Status') === 'CONFIRMED' && $request->boolean('Success')) {
            $this->markAsPaid($order, (string) $request->input('PaymentId'));
        }

        // Тинькофф ждёт чистый "OK"
        return response('OK', 200);
    }

    /**
     * Webhook от ЮKassa.
     *
     * ЮKassa шлёт POST с событием (payment.succeeded, payment.canceled, ...).
     * Тело запроса содержит объект платежа в `object`, включая сумму и metadata.
     *
     * Регистрируется как POST /payments/yookassa/callback — без auth, без CSRF.
     */
    public function callbackYooKassa(Request $request, PaymentGatewayInterface $paymentGateway): Response
    {
        $data = $request->all();

        // 1. Валидация: структура, событие, статус
        if (!$paymentGateway->validateCallback($data)) {
            Log::warning('YooKassa callback: invalid payload', $data);

            return response('Invalid callback', 400);
        }

        $object = $data['object'] ?? [];
        $orderId = $object['metadata']['order_id'] ?? null;

        if (!$orderId) {
            Log::warning('YooKassa callback: missing order_id', ['object' => $object]);

            return response('Order id missing', 400);
        }

        $order = Order::find($orderId);

        if (!$order) {
            Log::warning('YooKassa callback: order not found', ['order_id' => $orderId]);

            return response('Order not found', 404);
        }

        // 2. Сверка суммы: ЮKassa присылает в рублях строкой "638.00"
        $expectedAmount = number_format($order->total_price / 100, 2, '.', '');
        $receivedAmount = (string) ($object['amount']['value'] ?? '');

        if ($receivedAmount !== $expectedAmount) {
            Log::warning('YooKassa callback: amount mismatch', [
                'order_id' => $order->id,
                'expected' => $expectedAmount,
                'received' => $receivedAmount,
            ]);

            return response('Amount mismatch', 400);
        }

        // 3. Идемпотентно помечаем заказ оплаченным
        if ($order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'status' => OrderStatus::CONFIRMED,
                'payment_id' => (string) ($object['id'] ?? ''),
            ]);

            Log::info('Order marked as paid via YooKassa', [
                'order_id' => $order->id,
                'payment_id' => $object['id'] ?? null,
                'amount' => $receivedAmount,
            ]);
        }

        // 4. ЮKassa ждёт 200 OK и не поддерживает редиректы
        return response('OK', 200);
    }

    /**
     * Симуляция оплаты (dev/test).
     */
    public function fakeProcess(Order $order)
    {
        if (config('services.payment.driver') !== 'fake') {
            abort(404);
        }

        $this->markAsPaid($order, 'fake_trx_' . time());

        return Auth::check()
            ? redirect()->route('profile.orders.index')->with('success', 'Заказ оплачен (fake)')
            : redirect()->route('home')->with('success', 'Заказ оплачен (fake)');
    }

    /**
     * Единая точка пометки заказа как оплаченного.
     * Идемпотентно: повторный вызов ничего не меняет.
     */
    private function markAsPaid(Order $order, ?string $paymentId): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => OrderStatus::CONFIRMED,
            'payment_id' => $paymentId,
        ]);

        Log::info('Order marked as paid', [
            'order_id' => $order->id,
            'payment_id' => $paymentId,
        ]);
    }

    /**
     * Страница "спасибо" после возврата от ЮKassa.
     * Заказ мог быть ещё не помечен как оплаченный (вебхук идёт параллельно),
     * поэтому доверять payment_status здесь не стоит — просто показываем статус.
     */
    public function successYooKassa(Order $order)
    {
        if ($order->user_id && $order->user_id !== Auth::id()) {
            abort(403);
        }

        return inertia('Checkout/Success', [
            'order' => $order->load('items'),
            'seo' => $this->seo('Заказ оформлен', robots: 'noindex, nofollow'),
        ]);
    }
}
