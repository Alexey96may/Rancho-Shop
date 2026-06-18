<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\DTO\CategoryDataDTO;

class CategoryRequest extends FormRequest
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
        $category = $this->route('category');
        $categoryId = $category ? $category->id : null;

        return [
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:categories,slug,' . $categoryId,
            'icon'        => 'nullable|string|max:255',
            'type'        => 'required|string|in:product,animal',
            'description' => 'nullable|string',
            'sort_order'  => 'integer',
            'is_active'   => 'boolean',
        ];
    }

    public function toDto(): CategoryDataDTO
    {
        return CategoryDataDTO::fromRequest($this->validated());
    }
}
