<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadPageMediaRequest extends FormRequest
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
            'image' => 'required|image|mimes:jpeg,png,webp,gif|max:3072',
        ];
    }

    public function messages(): array
    {
        return [
            'image.max'   => 'Изображение слишком тяжелое (не более 3 МБ).',
            'image.image' => 'Файл должен быть картинкой.',
        ];
    }
}
