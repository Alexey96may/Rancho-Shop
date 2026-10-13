<?php

namespace App\Exceptions\Checkout;

use Illuminate\Validation\ValidationException;

abstract class CheckoutException extends ValidationException
{
    abstract public function code(): string;

    protected function errorKey(): string
    {
        return 'checkout';
    }
}
