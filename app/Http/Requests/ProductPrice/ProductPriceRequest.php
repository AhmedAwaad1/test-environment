<?php

namespace App\Http\Requests\ProductPrice;

use Illuminate\Foundation\Http\FormRequest;

class ProductPriceRequest extends FormRequest
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
            'product_id' => ['nullable', 'exists:products,id'],
            'currency_id' => ['nullable', 'exists:currencies,id'],
            'sort_by' => ['nullable', 'string', 'in:price,created_at'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    private function storeRules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'currency_id' => 'required|exists:currencies,id',
            'price' => 'required|numeric|min:0',
            'price_after_discount' => 'nullable|numeric|min:0',
        ];
    }

    private function updateRules(): array
    {
        return [
            'product_id' => 'nullable|exists:products,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'price' => 'nullable|numeric|min:0',
            'price_after_discount' => 'nullable|numeric|min:0',
        ];
    }
} 