<?php

namespace App\Mail;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminOrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Админ] Заказ #' . $this->order->id . ' — статус изменён',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.order-status',
            with: [
                'order' => $this->order,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'oldStatusLabel' => $this->statusLabel($this->oldStatus),
                'newStatusLabel' => $this->statusLabel($this->newStatus),
            ],
        );
    }

    private function statusLabel(string $value): string
    {
        return OrderStatus::tryFrom($value)?->label() ?? $value;
    }
}
