<?php

namespace App\Jobs;

use App\Mail\AdminAiChatContactMail;
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

class NotifyAdminAboutAiContactJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public readonly int $userMessageId,
        public readonly array $contact,   // ['type' => 'phone', 'value' => '+79781234567']
        public readonly ?int $userId,
        public readonly string $sessionId,
    ) {}

    public function handle(AiChatTelegramNotifier $telegramNotifier): void
    {
        $userMsg = AiMessage::with('user')->find($this->userMessageId);
        if (!$userMsg) {
            return;
        }

        // 1. Telegram
        try {
            $telegramNotifier->notifyContact($userMsg, $this->contact);
        } catch (Throwable $e) {
            Log::error('AI chat contact Telegram notify failed', ['message' => $e->getMessage()]);
        }

        // 2. Email
        $adminEmail = config('app.admin_email');
        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(
                    new AdminAiChatContactMail($userMsg, $this->contact)
                );
            } catch (Throwable $e) {
                Log::error('AI chat contact email notify failed', ['message' => $e->getMessage()]);
            }
        }
    }
}
