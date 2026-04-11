<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Exceptions\Checkout\CheckoutException;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (CheckoutException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'code' => $e->code(),
            ], 422);
        });

        $this->renderable(function (Throwable $e) {
            return response()->json([
                'message' => 'Server error',
            ], 500);
        });
    }
}