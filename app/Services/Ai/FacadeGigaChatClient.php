<?php

namespace App\Services\Ai;

use App\Contracts\Ai\GigaChatClient;
use Tigusigalpa\GigaChat\Laravel\GigaChat;

class FacadeGigaChatClient implements GigaChatClient
{
    public function chat(array $messages): array
    {
        $response = GigaChat::chat($messages);

        return [
            'content' => $response['choices'][0]['message']['content'] ?? 'Извините, не могу ответить.',
            'usage' => $response['usage'] ?? null,
            'model' => $response['model'] ?? null,
        ];
    }
}
