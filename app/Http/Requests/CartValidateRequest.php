<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use App\DTO\CartItemDTO;

class CartValidateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'items' => ['required', 'array'],
        'items.*.variant_id' => ['required', 'integer', 'exists:product_variants,id'],
        'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
    ];
    }

    public function toDTO(): Collection
    {
        return collect($this->validated()['items'])
            ->map(fn ($item) => CartItemDTO::fromArray($item));
    }
}
