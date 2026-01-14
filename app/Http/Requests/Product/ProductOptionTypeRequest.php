<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductOptionTypeRequest extends FormRequest
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
        ];
    }

    private function storeRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:product_option_types,name',
        ];
    }

    private function updateRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:product_option_types,name,' . $this->route('id'),
        ];
    }
}
