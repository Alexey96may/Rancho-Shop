<?php

namespace App\DTO;

class UserDataDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public string $role,
        public ?string $password
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            name: $validated['name'],
            email: mb_strtolower($validated['email'], 'UTF-8'),
            phone: $validated['phone'] ?? null,
            role: $validated['role'],
            password: $validated['password'] ?? null
        );
    }

    public function toArray(): array
    {
        // Filter out empty passwords to avoid accidentally overwriting the old ones during an update
        return array_filter([
            'name'     => $this->name,
            'email'    => $this->email,
            'phone'    => $this->phone,
            'role'     => $this->role,
            'password' => $this->password,
        ], fn($value) => $value !== null);
    }
}
