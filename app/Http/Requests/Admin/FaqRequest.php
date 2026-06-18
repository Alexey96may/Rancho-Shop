<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\DTO\FaqDataDTO;

class FaqRequest extends FormRequest
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
            'question'     => 'required|string|max:500',
            'answer'       => 'required|string',
            'is_published' => 'boolean',
            'sort_order'   => $this->isMethod('POST') ? 'nullable|integer' : 'required|integer',
        ];
    }

    public function toDto(): FaqDataDTO
    {
        return FaqDataDTO::fromRequest($this->validated());
    }
}
