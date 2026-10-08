<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Exceptions\Checkout\CheckoutException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (CheckoutException $e, $request) {
            Log::warning('Checkout business exception', [
                'message' => $e->getMessage(),
                'code' => $e->code(),
                'user_id' => $request->user()?->id,
            ]);

            // Inertia
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                throw ValidationException::withMessages([
                    'cart' => $e->getMessage(),
                ]);
            }

            // JSon
            return response()->json([
                'message' => $e->getMessage(),
                'code' => $e->code(),
            ], 422);
        });

        $this->renderable(function (Throwable $e, $request) {
            Log::error('Unhandled exception', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
            ]);
        });
    }
}
