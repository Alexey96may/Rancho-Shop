<?php

namespace App\Mail;

use App\Models\AiMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminAiChatEscalationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly AiMessage $userMessage,
        public readonly AiMessage $assistantMessage,
        public readonly string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[AI-чат] Требуется внимание — сессия ' . ($this->userMessage->session_id ?? '—'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.ai-chat-escalation',
            with: [
                'userMessage' => $this->userMessage,
                'assistantMessage' => $this->assistantMessage,
                'reason' => $this->reason,
            ],
        );
    }
}
