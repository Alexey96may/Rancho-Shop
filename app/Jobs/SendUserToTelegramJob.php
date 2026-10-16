<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Telegram\UserTelegramNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendUserToTelegramJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public readonly int $userId,
    ) {}

    public function handle(UserTelegramNotifier $notifier): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            return;
        }

        $notifier->notifyNewUser($user);
    }
}
