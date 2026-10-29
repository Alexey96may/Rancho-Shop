<?php

namespace App\Jobs;

use App\Mail\AdminAiChatEscalationMail;
use App\Models\AiMessage;
use App\Services\Telegram\AiChatTelegramNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotifyAdminAboutAiChatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public readonly int $userMessageId,
        public readonly int $assistantMessageId,
        public readonly string $reason,
    ) {}

    public function handle(AiChatTelegramNotifier $telegramNotifier): void
    {
        $userMsg = AiMessage::with('user')->find($this->userMessageId);
        $assistantMsg = AiMessage::find($this->assistantMessageId);

        if (!$userMsg || !$assistantMsg) {
            return;
        }

        // 1. Telegram
        try {
            $telegramNotifier->notifyEscalation($userMsg, $assistantMsg, $this->reason);
        } catch (Throwable $e) {
            Log::error('AI chat Telegram notify failed', ['message' => $e->getMessage()]);
        }

        // 2. Email
        $adminEmail = config('app.admin_email');

        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(
                    new AdminAiChatEscalationMail($userMsg, $assistantMsg, $this->reason)
                );
            } catch (Throwable $e) {
                Log::error('AI chat email notify failed', ['message' => $e->getMessage()]);
            }
        }
    }
}
