<?php

namespace App\Http\Requests;

use App\DTO\CartItemDTO;
use App\DTO\CheckoutDTO;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'customer_phone' => [
                'required',
                'string',
                'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/',
            ],
            'delivery_address' => ['nullable', 'string'],
            'customer_comment' => ['nullable', 'string'],
            'create_account' => ['sometimes', 'boolean'],

            'promo_code_id' => [
                'nullable',
                'integer',
                'exists:promo_codes,id',
            ],

            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer', 'exists:product_variants,id'],
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
            createAccount: (bool) ($data['create_account'] ?? false),
            promoCodeId: $data['promo_code_id'] ?? session('promo_code_id'),
            userId: $this->user()?->id,
            items: collect($data['items'])
                ->map(fn ($item) => new CartItemDTO(
                    quantity: $item['quantity'],
                    variantId: $item['variant_id'],
                )),
        );
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_phone' => $this->normalizePhone($this->input('customer_phone')),
        ]);
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        // 10 цифр без префикса → 7XXXXXXXXXX
        if (strlen($digits) === 10) {
            $digits = '7' . $digits;
        }

        // 8XXXXXXXXXX → 7XXXXXXXXXX
        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        }

        // 11 цифр, начинается с 7 → форматируем
        if (strlen($digits) === 11 && $digits[0] === '7') {
            return sprintf(
                '+7 (%s) %s-%s-%s',
                substr($digits, 1, 3),
                substr($digits, 4, 3),
                substr($digits, 7, 2),
                substr($digits, 9, 2),
            );
        }

        return $phone;
    }
}
