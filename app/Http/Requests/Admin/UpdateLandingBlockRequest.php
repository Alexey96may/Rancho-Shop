<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\DTO\LandingBlockDataDTO;

class UpdateLandingBlockRequest extends FormRequest
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
            'title'             => 'required|string|max:500',
            'subtitle'          => 'nullable|string|max:500',
            'is_visible'        => 'boolean',
            'content'           => 'required|array',
            'content.*.title'   => 'nullable|string|max:255',
            'content.*.desc'    => 'required|string',
            'content.*.icon'    => 'nullable|string',
            'content.*.step'    => 'nullable|integer',
        ];
    }

    public function toDto(): LandingBlockDataDTO
    {
        return LandingBlockDataDTO::fromRequest($this->validated());
    }
}
