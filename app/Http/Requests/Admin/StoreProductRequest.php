<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AvailabilityType;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\File;
use App\DTO\ProductDataDTO;

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
            'description' => 'nullable|string',
            'attributes' => 'nullable|array',
            'schedule' => 'nullable|array',
            'animal_ids' => 'array',
            'main_photo' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $validator = \Illuminate\Support\Facades\Validator::make(
                            [$attribute => $value],
                            [$attribute => \Illuminate\Validation\Rules\File::image()->max(5 * 1024)]
                        );

                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    }
                }
            ],
            'gallery' => 'nullable|array',
            'gallery.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $validator = \Illuminate\Support\Facades\Validator::make(
                            [$attribute => $value],
                            [$attribute => \Illuminate\Validation\Rules\File::image()->max(5 * 1024)]
                        );

                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    }
                }
            ],
            'remove_media' => 'nullable|array',
            'seo.title'       => 'nullable|string|max:255',
            'seo.description' => 'nullable|string',
            'seo.keywords'    => 'nullable|string',
            'seo.canonical'   => 'nullable|string|url',
            'seo.is_noindex'  => 'boolean',
        ];
    }

    /**
    * Get a strongly typed DTO from the request
    */
    public function toDto(): ProductDataDTO
    {
        return ProductDataDTO::fromRequest($this->validated());
    }
}
