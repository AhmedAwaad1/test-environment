<?php

namespace App\Http\Requests\Banner;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return match ($this->method()) {
            'GET' => $this->indexRules(),
            'POST' => $this->storeRules(),
            'PUT', 'PATCH' => $this->updateRules(),
            'DELETE' => [],
            default => [],
        };
    }


    private function indexRules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    private function storeRules(): array
    {
        return [
            'image'           => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'url'             => ['nullable', 'string', 'max:255'],
            'product_id'      => ['nullable', 'exists:products,id'],
            'category_id'     => ['nullable', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
        ];
    }

    private function updateRules(): array
    {
        return [
            'image'           => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'url'             => ['nullable', 'string', 'max:255'],
            'product_id'      => ['nullable', 'exists:products,id'],
            'category_id'     => ['nullable', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
        ];
    }
}
