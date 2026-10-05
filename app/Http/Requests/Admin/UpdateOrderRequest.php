<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\DTO\OrderDataDTO;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');
        $user = $this->user();

        if ($this->filled('admin_note') && $this->input('admin_note') !== $order->admin_note) {
            if (!$user || !$user->isAdmin()) {
                return false; // Automatically throws 403 Forbidden with default message
            }
        }

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
            'status'     => 'required|in:new,confirmed,delivering,completed,cancelled',
            'admin_note' => 'nullable|string|max:2000',
        ];
    }

    /**
    * Custom authorization error message (optional)
    */
    public function messages(): array
    {
        return [
            'status.in' => 'Указан некорректный статус заказа.',
        ];
    }

    public function toDto(): OrderDataDTO
    {
        return OrderDataDTO::fromRequest($this->validated());
    }
}
