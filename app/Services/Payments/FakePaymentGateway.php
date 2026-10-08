<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;

class FakePaymentGateway implements PaymentGatewayInterface
{
    /**
     * Возвращает тестовые данные для имитации оплаты
     */
    public function getPaymentData(Order $order): array
    {
        return [
            'type' => 'fake',
            'action_url' => route('payments.fake.process', $order),
            'params' => [],
        ];
    }

    /**
     * В фейковом режиме подпись всегда валидна
     */
    public function validateCallback(array $data): bool
    {
        return true;
    }
}
