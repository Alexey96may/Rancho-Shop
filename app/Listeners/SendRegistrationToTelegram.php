<?php

namespace App\Listeners;

use App\Jobs\SendUserToTelegramJob;
use Illuminate\Auth\Events\Registered;

class SendRegistrationToTelegram
{
    public function handle(Registered $event): void
    {
        if (!isset($event->user) || !$event->user->id) {
            return;
        }

        SendUserToTelegramJob::dispatch($event->user->id);
    }
}
