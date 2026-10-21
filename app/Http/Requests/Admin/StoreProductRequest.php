<?php

namespace App\Http\Requests\Admin;

use App\DTO\ProductDataDTO;
use App\Enums\AvailabilityType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\File;

class StoreProductRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'category_id' => 'required|exists:categories,id',
            'availability_type' => ['required', new Enum(AvailabilityType::class)],
            'is_active' => 'boolean',
            'is_commentable' => 'boolean',
            'description' => 'nullable|string',
            'schedule' => 'nullable|array',
            'schedule.next_delivery' => 'nullable|date|date_format:Y-m-d|after_or_equal:today',
            'schedule.interval_days' => 'nullable|integer|min:1|max:365',
            'schedule.note' => 'nullable|string|max:255',
            'attributes' => 'nullable|array',
            'attributes.*.label' => 'required|string|max:255',
            'attributes.*.value' => 'nullable|string|max:1000',
            'animal_ids' => 'array',
            'main_photo' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value instanceof UploadedFile) {
                        $validator = Validator::make(
                            [$attribute => $value],
                            [$attribute => File::image()->max(5 * 1024)]
                        );

                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    }
                },
            ],
            'gallery' => 'nullable|array',
            'gallery.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value instanceof UploadedFile) {
                        $validator = Validator::make(
                            [$attribute => $value],
                            [$attribute => File::image()->max(5 * 1024)]
                        );

                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    }
                },
            ],
            'remove_media' => 'nullable|array',
            'seo.title' => 'nullable|string|max:255',
            'seo.description' => 'nullable|string',
            'seo.keywords' => 'nullable|string',
            'seo.canonical' => 'nullable|string|url',
            'seo.is_noindex' => 'boolean',
        ];
    }

    /**
     * Get a strongly typed DTO from the request
     */
    public function toDto(?int $ignoreId = null): ProductDataDTO
    {
        return ProductDataDTO::fromRequest($this->validated(), $ignoreId);

    }
}
