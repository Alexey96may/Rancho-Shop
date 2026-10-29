<?php

namespace App\Actions\Ai;

use App\Jobs\NotifyAdminAboutAiChatJob;
use App\Jobs\NotifyAdminAboutAiContactJob;
use App\Models\AiMessage;
use App\Models\Animal;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConsultGigaChatAction
{
    private const HISTORY_LIMIT = 20;

    private const MAX_TOOL_ITERATIONS = 3;

    private const ESCALATION_KEYWORDS = [
        'человек', 'оператор', 'менеджер', 'позовите', 'соедините',
        'жалоба', 'проблема', 'не работает', 'обман',
    ];

    private const FALLBACK_PROMPT = <<<'PROMPT'
Ты — вежливый консультант интернет-магазина «Молочная Долина».

ЖЁСТКИЕ ПРАВИЛА:
- Ты НЕ знаешь ассортимент, цены и животных по памяти. Никогда не выдумывай названия товаров, цены, клички, породы.
- Если вопрос про цену, стоимость, наличие или ассортимент — ОБЯЗАТЕЛЬНО вызови get_product_price.
- Если вопрос про конкретное животное (корову, кота, козу) или «кто даёт молоко» — ОБЯЗАТЕЛЬНО вызови get_animal_info.
- Если вопрос про доставку — ОБЯЗАТЕЛЬНО вызови get_delivery_info.
- Если функция вернула found: false — честно скажи «не нашёл» и предложи посмотреть каталог. НЕ придумывай.
- Если не уверен — скажи «уточните в каталоге» или «не могу ответить».
PROMPT;

    private ?array $lastUsage = null;

    public function execute(string $question, ?int $userId, string $sessionId): string
    {
        $this->lastUsage = null;

        // 1. Проверяем, есть ли в сообщении контакт
        $contact = $this->extractContact($question);

        // 2. Проверяем, была ли недавно эскалация (по этой сессии)
        $wasEscalated = $this->wasEscalatedRecently($sessionId, $userId);

        // 3. Собираем промпт + историю
        $systemPrompt = Setting::where('key', 'ai_bot_prompt')->value('value')
            ?? self::FALLBACK_PROMPT;

        $history = AiMessage::historyFor($sessionId, $userId, self::HISTORY_LIMIT);

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $question]],
        );

        // 4. Спрашиваем GigaChat
        $answer = $this->chatWithTools($messages);

        // 5. Определяем причину эскалации
        $reason = $this->escalationReason($question, $answer);

        // 6. Если зовут человека — просим контакт
        if ($reason && $this->isHumanRequest($question)) {
            $answer .= "\n\nЧтобы менеджер связался с вами, оставьте, пожалуйста, телефон или email.";
        }

        // 7. Если пользователь оставил контакт (и была эскалация) — отвечаем «спасибо»
        $contactAccepted = $contact && $wasEscalated;

        if ($contactAccepted) {
            $answer = 'Спасибо! Передали ваш контакт менеджеру — он свяжется с вами в ближайшее время.';
        }

        // 8. Сохраняем сообщения
        $userMessage = AiMessage::create([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'role' => 'user',
            'content' => $question,
        ]);

        $assistantMessage = AiMessage::create([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'role' => 'assistant',
            'content' => $answer,
            'meta' => [
                'usage' => $this->lastUsage,
                'escalation_reason' => $reason,
                'contact' => $contactAccepted ? $contact : null,
            ],
        ]);

        // 9. Уведомляем админа об эскалации
        if ($reason) {
            NotifyAdminAboutAiChatJob::dispatch(
                $userMessage->id,
                $assistantMessage->id,
                $reason,
            );
        }

        // 10. Уведомляем админа о контакте
        if ($contactAccepted) {
            NotifyAdminAboutAiContactJob::dispatch(
                $userMessage->id,
                $contact,
                $userId,
                $sessionId,
            );
        }

        return $answer;
    }

    private function isHumanRequest(string $question): bool
    {
        $q = mb_strtolower($question, 'UTF-8');

        $keywords = ['человек', 'оператор', 'менеджер', 'позовите', 'соедините'];

        foreach ($keywords as $kw) {
            if (str_contains($q, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Цикл: отправляем в GigaChat, если он просит функцию — вызываем, отправляем результат обратно.
     */
    private function chatWithTools(array $messages): string
    {
        $tools = $this->tools();

        for ($i = 0; $i < self::MAX_TOOL_ITERATIONS; $i++) {
            $response = $this->callGigaChat($messages, $tools);

            if (empty($response)) {
                return 'Извините, не могу ответить.';
            }

            $this->lastUsage = $response['usage'] ?? null;
            $choice = $response['choices'][0]['message'] ?? null;

            if (!$choice) {
                return 'Извините, не могу ответить.';
            }

            // Модель просит вызвать функцию
            if (!empty($choice['tool_calls'])) {
                $messages[] = $choice;

                foreach ($choice['tool_calls'] as $toolCall) {
                    $functionName = $toolCall['function']['name'] ?? '';
                    $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?? [];

                    $result = $this->callFunction($functionName, $arguments);

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'] ?? '',
                        'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    ];
                }

                continue;
            }

            return $choice['content'] ?? 'Извините, не могу ответить.';
        }

        return 'Извините, не удалось получить ответ.';
    }

    /**
     * Вызов GigaChat API (OpenAI-совместимый).
     * Токен берём из кэша или получаем новый.
     */
    private function callGigaChat(array $messages, array $tools): array
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->timeout(30)
                ->withoutVerifying() // только для dev, на проде — свой сертификат
                ->post('https://api.giga.chat/v1/chat/completions', [
                    'model' => config('gigachat.default_model', 'GigaChat-2'),
                    'messages' => $messages,
                    'tools' => $tools,
                    'temperature' => 0.1,
                ]);

            if ($response->failed()) {
                Log::error('GigaChat chat failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('GigaChat exception: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Получение access_token (живёт 30 минут, кэшируем на 25).
     */
    private function getAccessToken(): string
    {
        return Cache::remember('gigachat_access_token', 25 * 60, function () {
            $authKey = config('gigachat.auth_key');

            if (!$authKey) {
                throw new \RuntimeException('GIGACHAT_AUTH_KEY is not configured');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $authKey,
                'RqUID' => (string) Str::uuid(),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
                ->asForm()
                ->withoutVerifying()
                ->post('https://ngw.devices.sberbank.ru:9443/api/v2/oauth', [
                    'scope' => config('gigachat.scope', 'GIGACHAT_API_PERS'),
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('GigaChat OAuth failed: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    private function tools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_product_price',
                    'description' => 'ОБЯЗАТЕЛЬНО вызывай, когда пользователь спрашивает про цену, стоимость, наличие или ассортимент любого товара. Никогда не отвечай про цены из памяти — только через эту функцию. Возвращает актуальную цену и наличие.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'product_name' => [
                                'type' => 'string',
                                'description' => 'Название товара (например, "молоко", "творог", "сыр", "молоко 3,2%")',
                            ],
                        ],
                        'required' => ['product_name'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_animal_info',
                    'description' => 'ОБЯЗАТЕЛЬНО вызывай, когда спрашивают про конкретное животное на ферме (корову, кота, козу) по кличке, или про то, кто даёт молоко/продукт. Никогда не выдумывай кличек, пород и статусов — только через эту функцию.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'animal_name' => [
                                'type' => 'string',
                                'description' => 'Имя, кличка животного или название продукта (например, "Зорька", "Бурёнка", "молоко")',
                            ],
                        ],
                        'required' => ['animal_name'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_delivery_info',
                    'description' => 'ОБЯЗАТЕЛЬНО вызывай, когда спрашивают про доставку, график, условия, минимальный заказ. Никогда не отвечай по памяти.',
                    'parameters' => ['type' => 'object', 'properties' => new \stdClass],
                ],
            ],
        ];
    }

    private function callFunction(string $name, array $args): array
    {
        Log::info('AI tool call', ['name' => $name, 'args' => $args]);

        $result = match ($name) {
            'get_product_price' => $this->getProductPrice($args['product_name'] ?? ''),
            'get_animal_info' => $this->getAnimalInfo($args['animal_name'] ?? ''),
            'get_delivery_info' => $this->getDeliveryInfo(),
            default => ['error' => 'Unknown function: ' . $name],
        };

        Log::info('AI tool result', ['name' => $name, 'result' => $result]);

        return $result;
    }

    private function getProductPrice(string $name): array
    {
        $needle = mb_strtolower(trim($name), 'UTF-8');

        $product = Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($needle) {
                $q->whereRaw('LOWER(products.name) LIKE ?', ["%{$needle}%"])
                    ->orWhereHas('variants', fn ($v) => $v->whereRaw('LOWER(variants.name) LIKE ?', ["%{$needle}%"]));
            })
            ->with(['variants' => fn ($q) => $q->orderByDesc('is_default')->orderBy('id')])
            ->first();

        if (!$product) {
            return ['found' => false, 'message' => 'Товар не найден'];
        }

        $variants = $product->variants->map(fn ($v) => [
            'name' => $v->name,
            'price_rub' => $v->priceRub(),
            'stock' => (float) $v->stock,
            'unit' => $v->unit?->short,
        ])->values()->toArray();

        $inStock = array_filter($variants, fn ($v) => $v['stock'] > 0);

        if (empty($inStock)) {
            return [
                'found' => true,
                'name' => $product->name,
                'in_stock' => false,
                'message' => 'Товар есть в каталоге, но сейчас нет в наличии',
                'variants' => $variants,
            ];
        }

        return [
            'found' => true,
            'name' => $product->name,
            'in_stock' => true,
            'variants' => array_values($inStock),
        ];
    }

    private function getAnimalInfo(string $name): array
    {
        $needle = mb_strtolower(trim($name), 'UTF-8');

        $animals = Animal::query()
            ->where('is_active', true)
            ->where(function ($q) use ($needle) {
                $q->whereRaw('LOWER(animals.name) LIKE ?', ["%{$needle}%"])
                    ->orWhereHas('products', fn ($p) => $p->whereRaw('LOWER(products.name) LIKE ?', ["%{$needle}%"]));
            })
            ->with([
                'category',
                'products.variants' => fn ($q) => $q->where('is_default', true),
            ])
            ->limit(5)
            ->get();

        if ($animals->isEmpty()) {
            return ['found' => false, 'message' => 'Животное не найдено'];
        }

        return [
            'found' => true,
            'count' => $animals->count(),
            'animals' => $animals->map(function (Animal $animal) {
                return [
                    'name' => $animal->name,
                    'category' => $animal->category?->name,
                    'status' => $animal->status,
                    'bio' => $animal->bio,
                    'features' => $animal->features,
                    'photo_url' => $animal->getFirstMediaUrl('avatars') ?: null,
                    'products' => $animal->products->map(function (Product $p) {
                        $variant = $p->variants->first();

                        return [
                            'name' => $p->name,
                            'price_rub' => $variant?->priceRub(),
                            'stock' => (float) ($variant?->stock ?? 0),
                            'unit' => $variant?->unit?->short,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray(),
        ];
    }

    private function getDeliveryInfo(): array
    {
        return [
            'schedule' => Setting::where('key', 'delivery_schedule')->value('value'),
            'info' => Setting::where('key', 'delivery_info')->value('value'),
            'min_order' => (int) Setting::where('key', 'min_order_amount')->value('value') / 100,
        ];
    }

    private function escalationReason(string $question, string $answer): ?string
    {
        $q = mb_strtolower($question, 'UTF-8');
        foreach (self::ESCALATION_KEYWORDS as $kw) {
            if (str_contains($q, $kw)) {
                return "Пользователь просит человека (ключевое слово: «{$kw}»)";
            }
        }

        $a = mb_strtolower($answer, 'UTF-8');
        if (str_contains($a, 'не знаю') || str_contains($a, 'не могу ответить')) {
            return 'Бот не смог ответить';
        }

        return null;
    }

    /**
     * Есть ли в тексте телефон или email.
     */
    private function extractContact(string $text): ?array
    {
        // Телефон: +7..., 8..., с пробелами/дефисами/скобками
        if (preg_match('/(\+?7|8)[\s\-\(]*\d{3}[\s\-\)]*\d{3}[\s\-]*\d{2}[\s\-]*\d{2}/u', $text, $m)) {
            return [
                'type' => 'phone',
                'value' => preg_replace('/\s+/', '', $m[0]),
            ];
        }

        // Email
        if (preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+/u', $text, $m)) {
            return [
                'type' => 'email',
                'value' => $m[0],
            ];
        }

        return null;
    }

    /**
     * Была ли эскалация в последнем ответе бота по этой сессии.
     */
    private function wasEscalatedRecently(string $sessionId, ?int $userId): bool
    {
        $lastAssistant = AiMessage::query()
            ->where('session_id', $sessionId)
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->where('role', 'assistant')
            ->latest('id')
            ->first();

        return $lastAssistant && !empty($lastAssistant->meta['escalation_reason']);
    }
}
