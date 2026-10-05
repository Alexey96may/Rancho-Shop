<?php

namespace App\DTO;

class OrderDataDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $status,
        public ?string $admin_note
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            status: $validated['status'],
            admin_note: $validated['admin_note'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'status'     => $this->status,
            'admin_note' => $this->admin_note,
        ];
    }
}
