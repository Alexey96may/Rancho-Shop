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
- Цена всегда за единицу (unit). Если step > 1 — укажи цену за единицу и итог за шаг.
  Пример: «Молоко — 200₽ за литр, банка 3л — 600₽».
PROMPT;

    private ?array $lastUsage = null;

    public function execute(string $question, ?int $userId, string $sessionId): string
    {
        $this->lastUsage = null;

        $contact = $this->extractContact($question);
        $wasEscalated = $this->wasEscalatedRecently($sessionId, $userId);

        $systemPrompt = Setting::where('key', 'ai_bot_prompt')->value('value')
            ?? self::FALLBACK_PROMPT;

        $history = AiMessage::historyFor($sessionId, $userId, self::HISTORY_LIMIT);

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $question]],
        );

        // Принудительный вызов функций (pre-flight) — ДО GigaChat
        $this->preFlightTools($question, $messages);

        $answer = $this->chatWithTools($messages);

        $reason = $this->escalationReason($question, $answer);

        if ($reason && $this->isHumanRequest($question)) {
            $answer .= "\n\nЧтобы менеджер связался с вами, оставьте, пожалуйста, телефон или email.";
        }

        $contactAccepted = $contact && $wasEscalated;

        if ($contactAccepted) {
            $answer = 'Спасибо! Передали ваш контакт менеджеру — он свяжется с вами в ближайшее время.';
        }

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

        if ($reason) {
            NotifyAdminAboutAiChatJob::dispatch(
                $userMessage->id,
                $assistantMessage->id,
                $reason,
            );
        }

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

        foreach (['человек', 'оператор', 'менеджер', 'позовите', 'соедините'] as $kw) {
            if (str_contains($q, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Динамические имена товаров из БД (кэш 5 минут).
     */
    private function knownProducts(): array
    {
        return Cache::remember('ai_known_products', 300, function () {
            return Product::query()
                ->where('is_active', true)
                ->pluck('name')
                ->map(fn ($n) => mb_strtolower($n, 'UTF-8'))
                ->unique()
                ->values()
                ->toArray();
        });
    }

    /**
     * Динамические клички животных из БД (кэш 5 минут).
     */
    private function knownAnimals(): array
    {
        return Cache::remember('ai_known_animals', 300, function () {
            return Animal::query()
                ->where('is_active', true)
                ->pluck('name')
                ->map(fn ($n) => mb_strtolower($n, 'UTF-8'))
                ->unique()
                ->values()
                ->toArray();
        });
    }

    /**
     * Принудительный вызов функций (для Lite-моделей).
     * Результат дописывается в последнее user-сообщение,
     * потому что GigaChat требует system только первым.
     */
    private function preFlightTools(string $question, array &$messages): void
    {
        try {
            $q = mb_strtolower($question, 'UTF-8');

            $appendData = function (array $result) use (&$messages) {
                $lastIndex = count($messages) - 1;
                if ($lastIndex >= 0 && ($messages[$lastIndex]['role'] ?? '') === 'user') {
                    $messages[$lastIndex]['content'] .=
                        "\n\n[Актуальные данные из базы, ОБЯЗАТЕЛЬНО используй в ответе, не выдумывай: "
                        . json_encode($result, JSON_UNESCAPED_UNICODE) . ']';
                }
            };

            // Цена / наличие
            if (preg_match('/(цена|стоит|стоимость|наличие|почём|почем|сколько)/u', $q)) {
                $productName = $this->extractProductName($q);
                if ($productName) {
                    $appendData($this->getProductPrice($productName));
                }
            }

            // Животное — если нашли кличку в БД
            $animalName = $this->extractAnimalName($q);
            if ($animalName) {
                $result = $this->getAnimalInfo($animalName);
                if ($result['found'] ?? false) {
                    $appendData($result);
                }
            }

            // Доставка
            if (preg_match('/(доставка|доставке|график|привоз|минимальный заказ)/u', $q)) {
                $appendData($this->getDeliveryInfo());
            }
        } catch (\Throwable $e) {
            Log::warning('preFlightTools failed', ['message' => $e->getMessage()]);
        }
    }

    private function extractProductName(string $q): ?string
    {
        // 1. Прямое вхождение названия из БД
        foreach ($this->knownProducts() as $name) {
            if (str_contains($q, $name)) {
                return $name;
            }
        }

        // 2. По отдельным словам («молоко 3,2%» → «молоко»)
        $words = preg_split('/\s+/u', $q);
        foreach ($words as $word) {
            $word = trim($word, " \t\n\r\0\x0B.,!?");
            if (mb_strlen($word) < 3) {
                continue;
            }
            foreach ($this->knownProducts() as $name) {
                if (str_contains($name, $word)) {
                    return $name;
                }
            }
        }

        // 3. Fallback: слово после «стоит/цена/сколько»
        $clean = preg_replace(
            '/^(?:сколько\s+)?(?:стоит|цена|стоимость|наличие|почём|почем|сколько)\s+(?:на\s+)?/ui',
            '',
            $q,
        );
        $clean = trim(preg_replace('/[?.,!].*$/u', '', trim($clean)));
        $clean = trim(preg_replace('/\b(пожалуйста|у вас|ваш[а-я]*)\b/ui', '', $clean));

        return $clean !== '' ? $clean : null;
    }

    private function extractAnimalName(string $q): ?string
    {
        // 1. Прямое вхождение клички из БД
        foreach ($this->knownAnimals() as $name) {
            if (str_contains($q, $name)) {
                return $name;
            }
        }

        // 2. Слово после «про»
        if (preg_match('/(?:про|о)\s+([а-яёa-z]{3,20})/ui', $q, $m)) {
            $word = mb_strtolower(trim($m[1]), 'UTF-8');
            foreach ($this->knownAnimals() as $name) {
                if (str_contains($name, $word) || str_contains($word, $name)) {
                    return $name;
                }
            }

            return $word;
        }

        return null;
    }

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

    private function callGigaChat(array $messages, array $tools): array
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->timeout(30)
                ->withoutVerifying()
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
                    'description' => 'ОБЯЗАТЕЛЬНО вызывай, когда пользователь спрашивает про цену, стоимость, наличие или ассортимент любого товара. Никогда не отвечай про цены из памяти — только через эту функцию.',
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
                    'description' => 'ОБЯЗАТЕЛЬНО вызывай, когда спрашивают про конкретное животное на ферме (корову, кота, козу) по кличке, или про то, кто даёт молоко. Никогда не выдумывай кличек — только через эту функцию.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'animal_name' => [
                                'type' => 'string',
                                'description' => 'Кличка животного (например, "Рыжик", "Зорька")',
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
                    'description' => 'ОБЯЗАТЕЛЬНО вызывай, когда спрашивают про доставку, график, условия, минимальный заказ.',
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

        if ($needle === '') {
            return ['found' => false, 'message' => 'Не указано название товара'];
        }

        $product = Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($needle) {
                $q->whereRaw('LOWER(products.name) LIKE ?', ["%{$needle}%"])
                    ->orWhereHas('variants', fn ($v) => $v->whereRaw('LOWER(product_variants.name) LIKE ?', ["%{$needle}%"]));
            })
            ->with(['variants' => fn ($q) => $q->orderByDesc('is_default')->orderBy('id')])
            ->first();

        if (!$product) {
            return ['found' => false, 'message' => 'Товар не найден'];
        }

        $variants = $product->variants->map(function ($v) {
            $pricePerUnit = $v->priceRub();
            $step = (float) $v->step;
            $unit = $v->unit?->short ?? 'шт';
            $pricePerStep = $pricePerUnit * $step;

            return [
                'name' => $v->name,
                'price_per_unit' => $pricePerUnit,
                'unit' => $unit,
                'step' => $step,
                'price_per_step' => $pricePerStep,
                'stock' => (float) $v->stock,
                'price_note' => $step > 1
                    ? "{$pricePerUnit}₽ за 1 {$unit}, продаётся кратно {$step} {$unit} (итого {$pricePerStep}₽ за {$step} {$unit})"
                    : "{$pricePerUnit}₽ за 1 {$unit}",
            ];
        })->values()->toArray();

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

        if ($needle === '') {
            return ['found' => false, 'message' => 'Не указано имя животного'];
        }

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
                        $pricePerUnit = $variant?->priceRub();
                        $step = (float) ($variant?->step ?? 1);
                        $unit = $variant?->unit?->short ?? 'шт';

                        return [
                            'name' => $p->name,
                            'price_per_unit' => $pricePerUnit,
                            'unit' => $unit,
                            'step' => $step,
                            'price_per_step' => $pricePerUnit !== null ? $pricePerUnit * $step : null,
                            'stock' => (float) ($variant?->stock ?? 0),
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

    private function extractContact(string $text): ?array
    {
        if (preg_match('/(\+?7|8)[\s\-\(]*\d{3}[\s\-\)]*\d{3}[\s\-]*\d{2}[\s\-]*\d{2}/u', $text, $m)) {
            return [
                'type' => 'phone',
                'value' => preg_replace('/\s+/', '', $m[0]),
            ];
        }

        if (preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+/u', $text, $m)) {
            return [
                'type' => 'email',
                'value' => $m[0],
            ];
        }

        return null;
    }

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
