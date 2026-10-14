<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\SettingService;

class DirectPaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly SettingService $settings,
    ) {}

    public function getPaymentData(Order $order): array
    {
        $payment = $this->settings->group([
            'payment_direct_phone',
            'payment_direct_bank',
            'payment_direct_recipient',
            'payment_direct_note_template',
        ]);

        $noteTemplate = $payment['payment_direct_note_template']
            ?? 'Оплата заказа #:order_id';

        return [
            'type' => 'direct',
            'details' => [
                'phone' => $payment['payment_direct_phone'],
                'bank' => $payment['payment_direct_bank'],
                'recipient' => $payment['payment_direct_recipient'],
                'note' => str_replace(
                    ':order_id',
                    (string) $order->id,
                    $noteTemplate
                ),
            ],
        ];
    }

    /**
     * Для ручных платежей автоматическая проверка подписи не требуется
     */
    public function validateCallback(array $data): bool
    {
        return true;
    }
}
