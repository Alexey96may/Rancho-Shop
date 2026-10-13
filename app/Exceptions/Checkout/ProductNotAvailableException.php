<?php

namespace App\Exceptions\Checkout;

use Illuminate\Validation\ValidationException;

class ProductNotAvailableException extends CheckoutException
{
    public function __construct(
        public int $productId,
        public ?string $productName = null,
    ) {
        $message = $productName
            ? "Товар \"{$productName}\" недоступен или снят с продажи."
            : "Товар (ID: {$productId}) недоступен для заказа.";

        $base = ValidationException::withMessages([
            $this->errorKey() => [$message],
        ]);

        parent::__construct($base->validator, $base->response, $base->errorBag);

        $this->message = $message;
    }

    public function code(): string
    {
        return 'PRODUCT_NOT_AVAILABLE';
    }

    protected function errorKey(): string
    {
        return 'cart';
    }
}
