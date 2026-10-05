<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\DTO\UnitDataDTO;

class UnitSaveRequest extends FormRequest
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
            'name'     => 'required|string|max:255',
            'short'    => 'required|string|max:50',
            'slug'     => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
        ];
    }

    public function toDto(): UnitDataDTO
    {
        $unit = $this->route('unit');
        return UnitDataDTO::fromRequest($this->validated(), $unit?->id);
    }
}
