<?php

namespace App\Http\Requests\ProductSetItems;

use Illuminate\Foundation\Http\FormRequest;

class ProductSetItemsRequest extends FormRequest
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
            'category_id' => ['nullable', 'exists:categories,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
            'sort_by' => ['nullable', 'string', 'in:name,created_at'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    private function storeRules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'sku' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
        ];
    }

    private function updateRules(): array
    {
        return [
            'category_id' => 'nullable|exists:categories,id',
            'product_ids' => 'nullable|array|min:1',
            'product_ids.*' => 'required_with:product_ids|exists:products,id',
            'sku' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
        ];
    }
} 