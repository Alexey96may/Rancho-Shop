<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramService
{
    public function __construct(
        private readonly ?string $botToken = null,
        private readonly ?string $adminChatId = null,
    ) {}

    public function sendToAdmin(string $text): bool
    {
        $chatId = $this->adminChatId ?? config('services.telegram.admin_chat_id');

        if (!$chatId) {
            return false;
        }

        return $this->sendMessage($chatId, $text);
    }

    public function sendMessage(string|int $chatId, string $text): bool
    {
        $token = $this->botToken ?? config('services.telegram.bot_token');

        if (!$token) {
            Log::warning('Telegram bot token is not configured');

            return false;
        }

        try {
            $response = Http::timeout(5)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

            if ($response->failed()) {
                Log::warning('Telegram sendMessage failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::error('Telegram sendMessage exception', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
