<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\DTO\UserDataDTO;

class UserSaveRequest extends FormRequest
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
        $user = $this->route('user');
        $userId = $user ? $user->id : null;

        return [
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'phone'    => 'nullable|string|max:20',
            'role'     => ['required', Rule::enum(UserRole::class)],
            // If a user is being created, a password is required; if it is being updated, it is optional.
            'password' => $userId ? ['nullable', Password::defaults()] : ['required', Password::defaults()],
        ];
    }

    public function toDto(): UserDataDTO
    {
        return UserDataDTO::fromRequest($this->validated());
    }
}
