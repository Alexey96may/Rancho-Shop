<?php

namespace App\DTO;

class PaymentDirectDTO
{
    public function __construct(
        public readonly ?string $phone,
        public readonly ?string $bank,
        public readonly ?string $recipient,
        public readonly ?string $noteTemplate,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            phone: $data['payment_direct_phone'] ?? null,
            bank: $data['payment_direct_bank'] ?? null,
            recipient: $data['payment_direct_recipient'] ?? null,
            noteTemplate: $data['payment_direct_note_template'] ?? 'Оплата заказа #:order_id',
        );
    }

    public function noteForOrder(int $orderId): string
    {
        return str_replace(':order_id', (string) $orderId, $this->noteTemplate);
    }
}
