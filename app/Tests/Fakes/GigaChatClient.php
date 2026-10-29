<?php

namespace Tests\Fakes;

use App\Contracts\Ai\GigaChatClient;

class FakeGigaChatClient implements GigaChatClient
{
    public array $sentMessages = [];

    public function __construct(private string $reply = 'Тестовый ответ') {}

    public function chat(array $messages): array
    {
        $this->sentMessages = $messages;

        return [
            'content' => $this->reply,
            'usage' => ['total_tokens' => 42],
            'model' => 'GigaChat',
        ];
    }
}
