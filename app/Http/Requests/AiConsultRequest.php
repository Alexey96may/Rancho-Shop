<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiConsultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:1000'],
            'session_id' => ['required', 'string', 'max:64'],
        ];
    }
}
