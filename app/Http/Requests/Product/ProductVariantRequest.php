<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->method()) {
            'GET' => $this->getRules(),
            'POST' => $this->storeRules(),
            'PUT', 'PATCH' => $this->updateRules(),
            default => [],
        };
    }

    private function getRules(): array
    {
        return [
            'option_value_ids' => ['required', 'array'],
            'option_value_ids.*' => ['required', 'exists:product_option_values,id'],
        ];
    }
    private function storeRules(): array
    {
        return [
            'sku' => ['required', 'string', 'unique:product_variants,sku'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_after_discount' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'barcode' => ['nullable', 'string', 'unique:product_variants,barcode'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'order' => ['nullable', 'integer', 'min:1'],
            'option_values' => ['required', 'array'],
            'option_values.*.id' => ['required', 'exists:product_option_values,id'],
        ];
    }

    private function updateRules(): array
    {
        return [
            'sku' => ['nullable', 'string', 'unique:product_variants,sku,' . $this->route('variantId')],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_after_discount' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'barcode' => ['nullable', 'string', 'unique:product_variants,barcode,' . $this->route('variantId')],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'order' => ['nullable', 'integer', 'min:1'],
            'option_values' => ['nullable', 'array'],
            'option_values.*.id' => ['required', 'exists:product_option_values,id'],
        ];
    }
}
