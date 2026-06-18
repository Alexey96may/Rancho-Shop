<?php

namespace App\DTO;

use App\Models\Faq;
use App\Services\SanitizeService;

class FaqDataDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $question,
        public string $answer,
        public bool $is_published,
        public int $sort_order
    ) {}

    public static function fromRequest(array $validated): self
    {
        $sortOrder = $validated['sort_order'] ?? null;
        if ($sortOrder === null) {
            $sortOrder = (Faq::max('sort_order') ?? 0) + 1;
        }

        return new self(
            question: $validated['question'],
            answer: SanitizeService::cleanHtml($validated['answer']),
            is_published: (bool) ($validated['is_published'] ?? false),
            sort_order: (int) $sortOrder
        );
    }

    public function toArray(): array
    {
        return [
            'question'     => $this->question,
            'answer'       => $this->answer,
            'is_published' => $this->is_published,
            'sort_order'   => $this->sort_order,
        ];
    }
}
