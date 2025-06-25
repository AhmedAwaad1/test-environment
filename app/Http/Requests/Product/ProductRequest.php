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
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'has_variants' => 'boolean',

            'images' => ['nullable', 'array'],
            'images.*.path' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'images.*.is_main' => ['nullable', 'boolean'],

            // Simple product fields (when has_variants = false)
            // 'price' => 'required|numeric|min:0',
            // 'price_after_discount' => 'required|numeric|min:0',
            'quantity' => 'required_if:has_variants,false|integer|min:0',
            'sku' => 'required_if:has_variants,false|string|unique:products,sku',

            // Options data (when has_variants = true)
            'options' => 'nullable|array',
            'options.*.option_type_id' => 'nullable|exists:product_option_types,id',
            'options.*.values' => 'nullable|array',
            'options.*.values.*.value' => 'nullable|string|max:255',
            'options.*.values.*.hex_code' => 'nullable|string|max:7',
            'options.*.values.*.order' => 'nullable|integer',
            'options.*.values.*.images' => 'nullable|array',
            'options.*.values.*.images.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            'variants' => 'nullable|array',
            'variants.*.sku' => 'nullable|string|unique:product_variants,sku',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.price_after_discount' => 'nullable|numeric|min:0',
            'variants.*.quantity' => 'nullable|integer|min:0',
            'variants.*.barcode' => 'nullable|string|unique:product_variants,barcode',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.is_active' => 'nullable|boolean',
            'variants.*.order' => 'nullable|integer|min:1',
            'variants.*.option_values' => 'nullable|array',
            'variants.*.option_values.*.value' => 'nullable|string|max:255',

            // Prices
            'prices' => ['nullable', 'array'],
            'prices.*.currency_id' => ['required', 'exists:currencies,id'],
            'prices.*.price' => ['required', 'numeric'],
            'prices.*.price_after_discount' => ['nullable', 'numeric'],
        ];
    }

    private function updateRules(): array
    {
        return [
            'name_en' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            // 'price' => ['nullable', 'numeric', 'min:0'],
            // 'price_after_discount' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'has_variants' => ['nullable', 'boolean'],

            // Images
            'images' => ['nullable', 'array'],
            'images.*.path' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'images.*.is_main' => ['nullable', 'boolean'],
            'deleted_images' => ['nullable', 'array'],
            'deleted_images.*' => ['exists:product_images,id'],

            // For simple products (no variants)
            'sku' => ['required_if:has_variants,false', 'string'],
            'quantity' => ['required_if:has_variants,false', 'integer', 'min:0'],

            // Options update
            'options' => ['required_if:has_variants,true', 'array'],
            'options.*.id' => ['nullable', 'exists:product_options,id'],
            'options.*.option_type_id' => ['required', 'exists:product_option_types,id'],
            'options.*.order' => ['nullable', 'integer'],
            'options.*.values' => ['required', 'array'],
            'options.*.values.*.id' => ['nullable', 'exists:product_option_values,id'],
            'options.*.values.*.value' => ['required', 'string', 'max:255'],
            'options.*.values.*.hex_code' => ['nullable', 'string', 'max:7'],
            'options.*.values.*.order' => ['nullable', 'integer'],
            'options.*.values.*.images' => ['nullable', 'array'],
            'options.*.values.*.images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],

            // Variants update
            'variants' => ['required_if:has_variants,true', 'nullable', 'array'],
            'variants.*.id' => ['nullable', 'exists:product_variants,id'],
            'variants.*.sku' => ['required', 'string'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.price_after_discount' => ['nullable', 'numeric', 'min:0'],
            'variants.*.quantity' => ['required', 'integer', 'min:0'],
            'variants.*.barcode' => ['nullable', 'string'],
            'variants.*.weight' => ['nullable', 'numeric', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
            'variants.*.order' => ['nullable', 'integer', 'min:1'],
            'variants.*.option_values' => ['required', 'array'],
            'variants.*.option_values.*.id' => ['required', 'exists:product_option_values,id'],
            'variants.*.option_values.*.value' => ['required', 'string'],

            // Prices
            'prices' => ['nullable', 'array'],
            'prices.*.currency_id' => ['required', 'exists:currencies,id'],
            'prices.*.price' => ['required', 'numeric'],
            'prices.*.price_after_discount' => ['nullable', 'numeric'],

        ];
    }
}
