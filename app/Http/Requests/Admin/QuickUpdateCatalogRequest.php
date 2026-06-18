<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class QuickUpdateCatalogRequest extends FormRequest
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
        $variant = $this->route('variant');

        return [
            'price' => 'sometimes|integer|min:0',
            'stock' => 'sometimes|integer|min:0',
            'is_default' => [
                'sometimes', 'boolean',
                function ($attribute, $value, $fail) use ($variant) {
                    if ($value === false && $variant->is_default && $variant->product->variants()->count() === 1) {
                        $fail('Нельзя снять флаг "По умолчанию", если это единственный вариант товара.');
                    }
                },
            ],
        ];
    }
}
