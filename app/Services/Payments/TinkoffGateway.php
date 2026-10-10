<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TinkoffGateway implements PaymentGatewayInterface
{
    private string $terminalKey;

    private string $secretKey;

    private string $apiUrl;

    public function __construct()
    {
        $this->terminalKey = (string) config('services.tinkoff.terminal_key');
        $this->secretKey = (string) config('services.tinkoff.secret_key');
        $this->apiUrl = rtrim((string) config('services.tinkoff.api_url'), '/') . '/';
    }

    /**
     * getPaymentData — вызывается из PaymentController::checkout()
     * Делает Init-запрос в Т-Банк и возвращает URL для редиректа.
     */
    public function getPaymentData(Order $order): array
    {
        $params = [
            'TerminalKey' => $this->terminalKey,
            'Amount' => (int) $order->total_price, // в копейках
            'OrderId' => (string) $order->id,
            'Description' => "Оплата заказа #{$order->id}",
            'NotificationURL' => route('payments.tinkoff.callback'),
            'SuccessURL' => route('payments.tinkoff.success', $order),
            'FailURL' => route('checkout.index'),
        ];

        $params['Token'] = $this->generateToken($params);

        $response = Http::timeout(15)
            ->when(!app()->isProduction(), fn ($http) => $http->withoutVerifying())
            ->post($this->apiUrl . 'Init', $params);

        Log::info('Tinkoff Init response', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if (!$response->ok()) {
            Log::error('Tinkoff Init failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'type' => 'error',
                'error' => 'Не удалось создать платёж. Попробуйте позже.',
            ];
        }

        $body = $response->json();

        if (!($body['Success'] ?? false)) {
            Log::warning('Tinkoff Init rejected', [
                'order_id' => $order->id,
                'body' => $body,
            ]);

            return [
                'type' => 'error',
                'error' => $body['Message'] ?? $body['Details'] ?? 'Ошибка платёжной системы',
            ];
        }

        return [
            'type' => 'redirect',
            'url' => $body['PaymentURL'],
        ];
    }

    /**
     * validateCallback — вызывается из PaymentController::callback()
     * Проверяет подпись и что платёж действительно для этого заказа.
     */
    public function validateCallback(array $data): bool
    {
        if (!isset($data['Token'])) {
            return false;
        }

        $receivedToken = (string) $data['Token'];

        // Формируем массив для проверки: только те поля, что пришли в вебхуке,
        // минус сам Token. Т-Банк присылает токен по тем же правилам,
        // что и Init, но набор полей может отличаться.
        $payload = $data;
        unset($payload['Token']);

        $expectedToken = $this->generateToken($payload);

        return hash_equals($expectedToken, $receivedToken);
    }

    /**
     * Генерация токена: все параметры (кроме Token) + Password, сортировка по ключам,
     * конкатенация значений, sha256.
     */
    private function generateToken(array $params): string
    {
        unset($params['Token']);
        $params['Password'] = $this->secretKey;

        ksort($params);

        // Т-Банк требует именно конкатенацию значений (без ключей)
        $values = implode('', array_map('strval', array_values($params)));

        return hash('sha256', $values);
    }
}
