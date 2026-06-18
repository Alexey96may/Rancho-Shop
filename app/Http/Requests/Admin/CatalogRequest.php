<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\DTO\CatalogDataDTO;


class CatalogRequest extends FormRequest
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
        $variant = $this->route('catalog'); 

        return [
            'product_id' => 'required|exists:products,id',
            'unit_id'    => 'required|exists:units,id',
            'name'       => 'required|string|max:255',
            'price'      => 'required|numeric|min:0',
            'old_price'  => 'nullable|numeric|min:0',
            'stock'      => 'required|integer|min:0',
            'position'   => 'nullable|integer',
            'attributes' => 'nullable|array',
            'is_default' => [
                'boolean',
                function ($attribute, $value, $fail) use ($variant) {
                    // Validation is triggered only when updating an existing record
                    if ($variant && $value === false && $variant->is_default && $variant->product->variants()->count() === 1) {
                        $fail('Нельзя снять флаг "По умолчанию", если это единственный вариант товара.');
                    }
                },
            ],
        ];
    }

    public function toDto(): CatalogDataDTO
    {
        return CatalogDataDTO::fromRequest($this->validated());
    }
}
