<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductAvailableMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Product $product,
        public readonly User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Товар «' . $this->product->name . '» снова в наличии',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.product-available',
            with: [
                'product' => $this->product,
                'user' => $this->user,
            ],
        );
    }
}
