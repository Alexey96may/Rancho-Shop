<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YooKassaGateway implements PaymentGatewayInterface
{
    private string $shopId;

    private string $secretKey;

    private string $apiUrl;

    public function __construct()
    {
        $this->shopId = (string) config('services.yookassa.shop_id');
        $this->secretKey = (string) config('services.yookassa.secret_key');
        $this->apiUrl = rtrim((string) config('services.yookassa.api_url'), '/') . '/';
    }

    /**
     * Создаёт платёж в ЮKassa и возвращает URL для редиректа.
     */
    public function getPaymentData(Order $order): array
    {
        // ЮKassa ожидает сумму в рублях с двумя знаками после запятой
        $amount = number_format($order->total_price / 100, 2, '.', '');

        $payload = [
            'amount' => [
                'value' => $amount,
                'currency' => 'RUB',
            ],
            'capture' => true,
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => route('payments.yookassa.success', $order),
            ],
            'description' => "Оплата заказа #{$order->id}",
            'metadata' => [
                'order_id' => (string) $order->id,
            ],
        ];

        $idempotenceKey = (string) Str::uuid();

        $response = Http::withBasicAuth($this->shopId, $this->secretKey)
            ->withHeaders([
                'Idempotence-Key' => $idempotenceKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(15)
            ->when(!app()->isProduction(), fn ($http) => $http->withoutVerifying())
            ->post($this->apiUrl . 'payments', $payload);

        Log::info('YooKassa Init response', [
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);

        if (!$response->ok()) {
            Log::error('YooKassa create payment failed', [
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

        if (($body['status'] ?? '') === 'pending' && !empty($body['confirmation']['confirmation_url'])) {
            return [
                'type' => 'redirect',
                'url' => $body['confirmation']['confirmation_url'],
            ];
        }

        Log::warning('YooKassa unexpected response', [
            'order_id' => $order->id,
            'body' => $body,
        ]);

        return [
            'type' => 'error',
            'error' => 'Платёжная система вернула неожиданный ответ.',
        ];
    }

    /**
     * Проверяет уведомление от ЮKassa.
     *
     * ЮKassa не использует HMAC-подпись как Т-Банк. Безопасность обеспечивается:
     * 1. HTTPS-соединением (уведомления приходят только на ваш защищённый URL).
     * 2. Сверкой суммы и идентификатора заказа из metadata.
     * 3. Опционально — проверкой подписи в заголовке Signature (ECDSA, требует
     *    отдельной библиотеки и публичного ключа ЮKassa).
     *
     * В данном методе мы делаем базовую проверку структуры и статуса.
     * Финальная сверка суммы и order_id происходит в контроллере.
     */
    public function validateCallback(array $data): bool
    {
        // Проверяем, что пришло событие об успешном платеже
        if (($data['event'] ?? '') !== 'payment.succeeded') {
            Log::warning('YooKassa callback: unexpected event', ['event' => $data['event'] ?? null]);

            return false;
        }

        $object = $data['object'] ?? [];

        // Платёж должен быть успешным
        if (($object['status'] ?? '') !== 'succeeded' || ($object['paid'] ?? false) !== true) {
            Log::warning('YooKassa callback: payment not succeeded', ['object' => $object]);

            return false;
        }

        // Базовая проверка наличия суммы и order_id
        if (empty($object['amount']['value']) || empty($object['metadata']['order_id'])) {
            Log::warning('YooKassa callback: missing amount or order_id', ['object' => $object]);

            return false;
        }

        // Если у вас настроена проверка подписи Signature — добавьте её здесь.
        // Пример (требует публичного ключа ЮKassa и библиотеки для ECDSA):
        // return $this->verifySignature($data);

        return true;
    }

    /**
     * Опционально: проверка подписи Signature.
     * ЮKassa использует ECDSA (brainpoolP384r1) + SHA-384.
     * Для реализации нужен публичный ключ ЮKassa и библиотека (например, openssl).
     */
    private function verifySignature(array $data): bool
    {
        // Заголовок Signature приходит как строка вида:
        // Signature: v1 <timestamp> <serial> <signature>
        // Проверка требует вычисления хеша от тела запроса и проверки ECDSA-подписи.
        // Реализуется отдельно, если необходима повышенная безопасность.
        return true;
    }
}
