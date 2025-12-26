<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
            // Accept 0,1,true,false
            'is_best_seller' => ['nullable', 'in:0,1,true,false'],
            'is_new_arrival' => ['nullable', 'in:0,1,true,false'],
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

            // Accept 0,1,true,false instead of boolean
            'has_variants' => ['required', 'in:0,1,true,false'],

            'is_best_seller' => ['nullable', 'in:0,1,true,false'],
            'is_new_arrival' => ['nullable', 'in:0,1,true,false'],

            'images' => ['nullable', 'array'],
            'images.*.path' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif,svg'],
            'images.*.is_main' => ['nullable', 'in:0,1,true,false'],

            'quantity' => 'required_if:has_variants,0|integer|min:0',
            'sku' => 'required_if:has_variants,0|string|unique:products,sku',

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
            'variants.*.is_active' => ['nullable', 'in:0,1,true,false'],
            'variants.*.order' => 'nullable|integer|min:1',
            'variants.*.option_values' => 'nullable|array',
            'variants.*.option_values.*.value' => 'nullable|string|max:255',

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

            'category_id' => ['nullable', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],

            'has_variants' => ['nullable', 'in:0,1,true,false'],
            'is_best_seller' => ['nullable', 'in:0,1,true,false'],
            'is_new_arrival' => ['nullable', 'in:0,1,true,false'],

            'images' => ['nullable', 'array'],
            'images.*.path' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg'],
            'images.*.is_main' => ['nullable', 'in:0,1,true,false'],
            'deleted_images' => ['nullable', 'array'],
            'deleted_images.*' => ['exists:product_images,id'],

            'sku' => ['required_if:has_variants,0', 'string'],
            'quantity' => ['required_if:has_variants,0', 'integer', 'min:0'],

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
            'variants.*.is_active' => ['nullable', 'in:0,1,true,false'],
            'variants.*.order' => 'nullable|integer|min:1',
            'variants.*.option_values' => 'nullable|array',
            'variants.*.option_values.*.value' => 'nullable|string|max:255',

            'prices' => ['nullable', 'array'],
            'prices.*.currency_id' => ['required', 'exists:currencies,id'],
            'prices.*.price' => ['required', 'numeric'],
            'prices.*.price_after_discount' => ['nullable', 'numeric'],
        ];
    }
}
