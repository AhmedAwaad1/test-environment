<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
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
        // Support both single and bulk formats
        // If 'items' array is provided, it's bulk mode; otherwise, use single mode
        if ($this->has('items') && is_array($this->input('items'))) {
            return [
                'session_id' => ['nullable', 'string'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required_without_all:items.*.product_variant_id,items.*.product_set_item_id', 'nullable', 'exists:products,id'],
                'items.*.product_variant_id' => ['required_without_all:items.*.product_id,items.*.product_set_item_id', 'nullable', 'exists:product_variants,id'],
                'items.*.product_set_item_id' => ['required_without_all:items.*.product_id,items.*.product_variant_id', 'nullable', 'exists:product_set_items,id'],
                'items.*.selected_product_ids' => ['nullable', 'array'],
                'items.*.selected_product_ids.*' => ['exists:products,id'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
            ];
        }
        
        // Single product format (backward compatible)
        return [
            'session_id'         => ['nullable', 'string'],
            'product_id'         => ['required_without_all:product_variant_id,product_set_item_id', 'nullable', 'exists:products,id'],
            'product_variant_id' => ['required_without_all:product_id,product_set_item_id', 'nullable', 'exists:product_variants,id'],
            'product_set_item_id' => ['required_without_all:product_id,product_variant_id', 'nullable', 'exists:product_set_items,id'],
            'selected_product_ids' => ['nullable', 'array'],
            'selected_product_ids.*' => ['exists:products,id'],
            'quantity'           => ['required', 'integer', 'min:1'],
        ];
    }

    private function updateRules(): array
    {
        return [
            'session_id'         => ['nullable', 'string'],
            'quantity'        => ['required', 'integer', 'min:1'],
        ];
    }
}
