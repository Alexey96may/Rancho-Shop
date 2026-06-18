<?php

namespace App\Exceptions\Checkout;

use Exception;

abstract class CheckoutException extends Exception
{
    abstract public function code(): string;
}
