<?php

namespace App\DTO;

class CatalogDataDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public int $product_id,
        public int $unit_id,
        public string $name,
        public int $price,
        public ?int $old_price,
        public int $stock,
        public bool $is_default,
        public int $position,
        public array $attributes
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            product_id: (int) $validated['product_id'],
            unit_id: (int) $validated['unit_id'],
            name: $validated['name'],
            price: (int) $validated['price'],
            old_price: isset($validated['old_price']) ? (int) $validated['old_price'] : null,
            stock: (int) $validated['stock'],
            is_default: (bool) ($validated['is_default'] ?? false),
            position: (int) ($validated['position'] ?? 0),
            attributes: $validated['attributes'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->product_id,
            'unit_id'    => $this->unit_id,
            'name'       => $this->name,
            'price'      => $this->price,
            'old_price'  => $this->old_price,
            'stock'      => $this->stock,
            'is_default' => $this->is_default,
            'position'   => $this->position,
            'attributes' => $this->attributes,
        ];
    }
}
