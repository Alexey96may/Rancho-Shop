<?php

namespace App\Contracts\Ai;

interface GigaChatClient
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{content: string, usage: ?array, model: ?string}
     */
    public function chat(array $messages): array;
}
