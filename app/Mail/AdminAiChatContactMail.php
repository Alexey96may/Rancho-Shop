<?php

namespace App\Mail;

use App\Models\AiMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminAiChatContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly AiMessage $userMessage,
        public readonly array $contact,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[AI-чат] Пользователь оставил контакт',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.ai-chat-contact',
            with: [
                'userMessage' => $this->userMessage,
                'contact' => $this->contact,
            ],
        );
    }
}
