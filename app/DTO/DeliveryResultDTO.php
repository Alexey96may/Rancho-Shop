<?php

class DeliveryResult
{
    public function __construct(
        public bool $isValid,
        public int $deliveryPrice,
        public ?array $zone = null,
    ) {}
}
