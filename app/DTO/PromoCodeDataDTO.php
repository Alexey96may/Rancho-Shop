<?php

namespace App\DTO;

use Carbon\Carbon;

class PromoCodeDataDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $code,
        public ?string $description,
        public string $type,
        public int $value,
        public ?int $min_order_amount,
        public ?int $max_discount,
        public ?int $usage_limit,
        public ?string $expires_at,
        public bool $is_active
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            code: strtoupper($validated['code']),
            description: $validated['description'] ?? null,
            type: $validated['type'],
            value: (int) $validated['value'],
            min_order_amount: isset($validated['min_order_amount']) ? (int) $validated['min_order_amount'] : null,
            max_discount: isset($validated['max_discount']) ? (int) $validated['max_discount'] : null,
            usage_limit: isset($validated['usage_limit']) ? (int) $validated['usage_limit'] : null,
            expires_at: $validated['expires_at'] ?? null,
            is_active: (bool) ($validated['is_active'] ?? false)
        );
    }

    public function toArray(): array
    {
        return [
            'code'             => $this->code,
            'description'      => $this->description,
            'type'             => $this->type,
            'value'            => $this->value,
            'min_order_amount' => $this->min_order_amount,
            'max_discount'     => $this->max_discount,
            'usage_limit'      => $this->usage_limit,
            'expires_at'       => $this->expires_at,
            'is_active'        => $this->is_active,
        ];
    }
}
