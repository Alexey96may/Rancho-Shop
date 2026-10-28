<?php

namespace App\Http\Requests;

use App\Enums\CommentableType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreCommentRequest extends FormRequest
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
        $user = $this->user();

        return [
            'commentable_id' => ['nullable', 'integer'],
            'commentable_type' => ['nullable', 'string', new Enum(CommentableType::class)],
            'content' => ['required', 'string', 'min:1'],
            'rating' => ['nullable', 'numeric', 'between:1,5'],
            'guest_name' => [Rule::requiredIf(!$user), 'nullable', 'string', 'max:50'],
            'agreement' => ['required', 'accepted'],
        ];
    }
}
