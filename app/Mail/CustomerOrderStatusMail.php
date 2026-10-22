<?php

namespace App\Mail;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerOrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $newStatus,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ваш заказ #' . $this->order->id . ' — статус обновлён',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.order-status',
            with: [
                'order' => $this->order,
                'newStatus' => $this->newStatus,
                'newStatusLabel' => $this->statusLabel($this->newStatus),
            ],
        );
    }

    private function statusLabel(string $value): string
    {
        return OrderStatus::tryFrom($value)?->label() ?? $value;
    }
}
