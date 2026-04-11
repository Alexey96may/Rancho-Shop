<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\DTO\CheckoutDTO;
use App\DTO\CartItemDTO;

class CheckoutRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'delivery_address' => ['nullable', 'string'],
            'customer_comment' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function toDTO(): CheckoutDTO
    {
        $data = $this->validated();

        return new CheckoutDTO(
            customerName: $data['customer_name'],
            customerPhone: $data['customer_phone'],
            deliveryAddress: $data['delivery_address'] ?? null,
            customerComment: $data['customer_comment'] ?? null,

            items: collect($data['items'])
                ->map(fn ($item) => new CartItemDTO(
                    productId: $item['product_id'],
                    quantity: $item['quantity']
                )),
        );
    }
}
