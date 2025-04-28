<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description_en' => ['required', 'string'],
            'description_ar' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_after_discount' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'product_type_id' => ['required', 'exists:product_types,id'],

            'images' => ['nullable', 'array'],
            'images.*.path' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'images.*.is_main' => ['nullable', 'boolean'],
        ];
    }

    private function updateRules(): array
    {
        return [
            'name_en' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_after_discount' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'product_type_id' => ['nullable', 'exists:product_types,id'],

            'images' => 'nullable|array',
            'images.*.path' => 'required|string',
            'main_image_id' => 'nullable|exists:images,id',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'exists:images,id',
        ];
    }
}
