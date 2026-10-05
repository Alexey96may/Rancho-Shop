<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;

class UpdateProductRequest extends StoreProductRequest
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
        $rules = parent::rules();
        // Modify the slug rule to ignore the current product
        $productId = $this->route('product')?->id;
        $rules['slug'] = "nullable|string|max:255|unique:products,slug,{$productId}";

        return $rules;
    }
}
