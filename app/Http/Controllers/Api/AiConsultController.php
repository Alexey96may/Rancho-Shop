<?php

namespace App\Http\Controllers\Api;

use App\Actions\Ai\ConsultGigaChatAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiConsultRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiConsultController extends Controller
{
    public function __construct(
        private readonly ConsultGigaChatAction $consultAction,
    ) {}

    public function consult(AiConsultRequest $request): JsonResponse
    {
        // --- Honeypot: защита от ботов ---
        if ($this->isSpam($request)) {
            return response()->json(
                ['answer' => 'Извините, произошла ошибка. Попробуйте позже.'],
                422,
            );
        }

        try {
            $answer = $this->consultAction->execute(
                question: $request->validated('question'),
                userId: $request->user()?->id,
                sessionId: $request->validated('session_id'),
            );

            return response()->json(['answer' => $answer]);
        } catch (Throwable $e) {
            Log::error('GigaChat error: ' . $e->getMessage());

            return response()->json(
                ['answer' => 'Извините, произошла ошибка. Попробуйте позже.'],
                500,
            );
        }
    }

    /**
     * Проверка honeypot: скрытое поле должно быть пустым,
     * а зашифрованное время — «свежим» (не старше N секунд).
     */
    private function isSpam(AiConsultRequest $request): bool
    {
        if (!config('honeypot.enabled', true)) {
            return false;
        }

        $nameField = config('honeypot.name_field_name', 'my_name');
        $validFromField = config('honeypot.valid_from_field_name', 'valid_from');
        $minSeconds = (int) config('honeypot.amount_of_seconds', 1);

        // 1. Бот заполнил скрытое поле
        if ($request->filled($nameField)) {
            Log::warning('AI chat honeypot: hidden field filled', [
                'value' => $request->input($nameField),
            ]);

            return true;
        }

        // 2. Проверка времени: encryptedValidFrom расшифровываем, сравниваем с now()
        $encrypted = $request->input($validFromField);

        if (!$encrypted) {
            return true; // поле отсутствует — подозрительно
        }

        try {
            $validFrom = Crypt::decryptString($encrypted);
        } catch (Throwable $e) {
            Log::warning('AI chat honeypot: cannot decrypt valid_from', [
                'message' => $e->getMessage(),
            ]);

            return true;
        }

        $diff = now()->timestamp - (int) $validFrom;

        // Если форма отправлена быстрее, чем за N секунд — бот
        if ($diff < $minSeconds) {
            Log::warning('AI chat honeypot: too fast', ['diff' => $diff]);

            return true;
        }

        return false;
    }
}
