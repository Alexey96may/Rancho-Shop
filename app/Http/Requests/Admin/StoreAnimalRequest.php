<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Validator;
use App\DTO\AnimalDataDTO;

class StoreAnimalRequest extends FormRequest
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
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'parent_id'   => 'nullable|exists:animals,id',
            'status'      => 'required|string',
            'bio'         => 'nullable|string',
            'features'    => 'nullable|array',
            'is_active'   => 'boolean',
            'remove_media'=> 'nullable|array',
            'avatar' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $validator = Validator::make([$attribute => $value], [
                            $attribute => File::image()->max(5 * 1024)
                        ]);
                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    }
                }
            ],
            'voice' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $validator = Validator::make([$attribute => $value], [
                            $attribute => File::types(['mp3', 'wav'])->max(20 * 1024)
                        ]);
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
                        $validator = Validator::make([$attribute => $value], [
                            $attribute => File::image()->max(10 * 1024)
                        ]);
                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    }
                }
            ],

            // SEO
            'seo.title'       => 'nullable|string|max:255',
            'seo.description' => 'nullable|string',
            'seo.keywords'    => 'nullable|string',
            'seo.canonical'   => 'nullable|string|url',
            'seo.is_noindex'  => 'boolean',
        ];
    }

    public function toDto(): AnimalDataDTO
    {
        return AnimalDataDTO::fromRequest($this->validated());
    }
}
