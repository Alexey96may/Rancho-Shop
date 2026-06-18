<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Enums\PromoCodeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\DTO\PromoCodeDataDTO;

class PromoCodeSaveRequest extends FormRequest
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
        $promocode = $this->route('promocode');
        $promocodeId = $promocode ? $promocode->id : null;

        return [
            'code'             => "required|string|max:50|unique:promo_codes,code,{$promocodeId}",
            'description'      => 'nullable|string|max:1000',
            'type'             => ['required', new Enum(PromoCodeType::class)],
            'value'            => 'required|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:0',
            'max_discount'     => 'nullable|integer|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'expires_at'       => 'nullable|date',
            'is_active'        => 'boolean',
            'create_another'   => 'boolean',
            'return_page'      => 'nullable',
        ];
    }

    public function toDto(): PromoCodeDataDTO
    {
        return PromoCodeDataDTO::fromRequest($this->validated());
    }
}
