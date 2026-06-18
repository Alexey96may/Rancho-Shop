<?php

namespace App\Http\Requests\Admin;

use App\Enums\PageType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\DTO\PageDataDTO;

class PageSaveRequest extends FormRequest
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
        $page = $this->route('page');
        $pageId = $page ? $page->id : null;

        return [
            'title'           => 'required|string|max:255',
            'content'         => 'nullable|string',
            'type'            => ['required', new Enum(PageType::class)],
            'is_active'       => 'boolean',
            'template'        => 'required|string|in:default,delivery,about',
            'slug'            => 'nullable|string|max:255|unique:pages,slug,' . $pageId,
            
            'seo.title'       => 'nullable|string|max:255',
            'seo.description' => 'nullable|string',
            'seo.keywords'    => 'nullable|string',
            'seo.canonical'   => 'nullable|string|url',
            'seo.is_noindex'  => 'boolean',
        ];
    }

    public function toDto(): PageDataDTO
    {
        return PageDataDTO::fromRequest($this->validated());
    }
}
