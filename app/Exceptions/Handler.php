<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Exceptions\Checkout\CheckoutException;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (CheckoutException $e) {
            Log::error('Checkout exception', [
                'message' => $e->getMessage(),
                'code' => $e instanceof CheckoutException ? $e->code() : null,
            ]);

            return response()->json([
                'message' => $e->getMessage(),
                'code' => $e->code(),
            ], 422);
        });

        $this->renderable(function (Throwable $e) {
            Log::error('Unhandled exception', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
            ]);

            return response()->json([
                'message' => 'Server error',
            ], 500);
        });
    }
}