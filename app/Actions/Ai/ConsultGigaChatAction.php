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

    private const FALLBACK_PROMPT = 'Ты — вежливый консультант интернет-магазина. Отвечай кратко.';

    private const MAX_TOOL_ITERATIONS = 3;

    private const ESCALATION_KEYWORDS = [
        'человек', 'оператор', 'менеджер', 'позовите', 'соедините',
        'жалоба', 'проблема', 'не работает', 'обман',
    ];

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

    private ?array $lastUsage = null;

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
                    'model' => 'GigaChat',
                    'messages' => $messages,
                    'tools' => $tools,
                    'temperature' => 0.3,
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
            $authKey = config('services.gigachat.auth_key');

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
                    'scope' => config('services.gigachat.scope', 'GIGACHAT_API_PERS'),
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
                    'description' => 'Возвращает актуальную цену и наличие товара по названию. Используй, когда спрашивают цену, стоимость или наличие продукта.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'product_name' => [
                                'type' => 'string',
                                'description' => 'Название товара (например, "молоко", "творог", "сыр")',
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
                    'description' => 'Возвращает информацию о животном на ферме по имени или кличке: вид, порода, статус, что производит, фото. Используй, когда спрашивают про конкретное животное (корову, козу и т.д.) или про то, кто даёт молоко.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'animal_name' => [
                                'type' => 'string',
                                'description' => 'Имя или кличка животного (например, "Зорька", "Бурёнка")',
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
                    'description' => 'Возвращает график доставки и условия. Используй, когда спрашивают про доставку.',
                    'parameters' => ['type' => 'object', 'properties' => new \stdClass],
                ],
            ],
        ];
    }

    private function callFunction(string $name, array $args): array
    {
        return match ($name) {
            'get_product_price' => $this->getProductPrice($args['product_name'] ?? ''),
            'get_animal_info' => $this->getAnimalInfo($args['animal_name'] ?? ''),
            'get_delivery_info' => $this->getDeliveryInfo(),
            default => ['error' => 'Unknown function: ' . $name],
        };
    }

    private function getProductPrice(string $name): array
    {
        $product = Product::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($name, 'UTF-8') . '%'])
            ->with(['variants' => fn ($q) => $q->where('stock', '>', 0)])
            ->first();

        if (!$product) {
            return ['found' => false, 'message' => 'Товар не найден'];
        }

        return [
            'found' => true,
            'name' => $product->name,
            'variants' => $product->variants->map(fn ($v) => [
                'name' => $v->name,
                'price' => $v->price,
                'price_rub' => $v->priceRub(),
                'stock' => (float) $v->stock,
                'unit' => $v->unit?->short,
            ])->toArray(),
        ];
    }

    private function getAnimalInfo(string $name): array
    {
        $animal = Animal::query()
            ->where('is_active', true)
            ->where(function ($q) use ($name) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($name, 'UTF-8') . '%'])
                    ->orWhereHas('products', fn ($p) => $p->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($name, 'UTF-8') . '%']));
            })
            ->with(['category', 'products.variants' => fn ($q) => $q->where('is_default', true)])
            ->first();

        if (!$animal) {
            return ['found' => false, 'message' => 'Животное не найдено'];
        }

        return [
            'found' => true,
            'name' => $animal->name,
            'category' => $animal->category?->name,
            'status' => $animal->status,
            'bio' => $animal->bio,
            'features' => $animal->features,
            'photo_url' => $animal->getFirstMediaUrl('avatars') ?: null,
            'products' => $animal->products->map(fn ($p) => [
                'name' => $p->name,
                'price_rub' => $p->mainVariant()?->priceRub(),
                'stock' => (float) ($p->mainVariant()?->stock ?? 0),
                'unit' => $p->mainVariant()?->unit?->short,
            ])->toArray(),
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
