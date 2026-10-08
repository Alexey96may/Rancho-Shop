<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;

class PayMasterGateway implements PaymentGatewayInterface
{
    private string $merchantId;
    private string $secretKey;

    public function __construct()
    {
        $this->merchantId = config('services.paymaster.merchant_id', '');
        $this->secretKey = config('services.paymaster.secret_key', '');
    }

    /**
     * Формирует массив параметров и подпись для формы оплаты PayMaster
     */
    public function getPaymentData(Order $order): array
    {
        $amount = number_format($order->total_price, 2, '.', '');
        $currency = 'RUB';

        $params = [
            'LMI_MERCHANT_ID' => $this->merchantId,
            'LMI_PAYMENT_AMOUNT' => $amount,
            'LMI_CURRENCY' => $currency,
            'LMI_PAYMENT_NO' => (string) $order->id,
            'LMI_PAYMENT_DESC' => "Оплата заказа #{$order->id}",
            'LMI_PAYMENT_NOTIFICATION_URL' => route('payments.paymaster.callback'),
            'LMI_SUCCESS_URL' => route('profile.orders.index'),
            'LMI_FAILURE_URL' => route('checkout.index'),
        ];

        // Строка для хеша: MerchantId + PaymentNo + Amount + Currency + SecretKey
        $stringToHash = implode(';', [
            $this->merchantId,
            $order->id,
            $amount,
            $currency,
            $this->secretKey,
        ]);

        $params['LMI_HASH'] = base64_encode(hash('sha256', $stringToHash, true));

        return [
            'type' => 'redirect',
            'action_url' => 'https://paymaster.ru/Payment/Init',
            'params' => $params,
        ];
    }

    /**
     * Проверяет подпись LMI_HASH, пришедшую в Webhook от PayMaster
     */
    public function validateCallback(array $data): bool
    {
        if (!isset($data['LMI_HASH'], $data['LMI_PAYMENT_NO'], $data['LMI_PAYMENT_AMOUNT'])) {
            return false;
        }

        $stringToHash = implode(';', [
            $data['LMI_MERCHANT_ID'] ?? '',
            $data['LMI_PAYMENT_NO'],
            $data['LMI_SYS_PAYMENT_ID'] ?? '',
            $data['LMI_SYS_PAYMENT_DATE'] ?? '',
            $data['LMI_PAYMENT_AMOUNT'],
            $data['LMI_CURRENCY'] ?? 'RUB',
            $data['LMI_PAID_AMOUNT'] ?? '',
            $data['LMI_PAYER_IDENTIFIER'] ?? '',
            $this->secretKey,
        ]);

        $expectedHash = base64_encode(hash('sha256', $stringToHash, true));

        return hash_equals($expectedHash, $data['LMI_HASH']);
    }
}
