<?php

namespace App\Http\Requests;

use App\DTO\CartItemDTO;
use App\DTO\CheckoutDTO;
use App\Models\ProductVariant;
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
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    // Извлекаем индекс: items.0.quantity → 0
                    $index = explode('.', $attribute)[1];
                    $variantId = $this->input("items.{$index}.variant_id");

                    if (!$variantId) {
                        return;
                    }

                    $variant = ProductVariant::find($variantId);
                    if (!$variant) {
                        return; // exists-правило уже отработает
                    }

                    $step = (float) $variant->step;
                    $stock = (float) $variant->stock;
                    $qty = (float) $value;

                    // 1. Остаток
                    if ($qty > $stock) {
                        $fail("Количество ({$qty}) превышает остаток ({$stock}).");

                        return;
                    }

                    // 2. Кратность шагу
                    if ($step > 0) {
                        $remainder = fmod($qty, $step);
                        // fmod(0.5, 0.1) может дать 0.0999999 — допуск
                        $epsilon = 1e-9;
                        if ($remainder > $epsilon && abs($remainder - $step) > $epsilon) {
                            $fail("Количество ({$qty}) должно быть кратно шагу ({$step}).");

                            return;
                        }
                    }

                    // 3. (Опционально) quantity >= step
                    if ($step > 0 && $qty < $step && $stock >= $step) {
                        $fail("Минимальное количество — {$step}.");
                    }
                },
            ],
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
