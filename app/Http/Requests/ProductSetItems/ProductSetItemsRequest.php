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
        ];
    }

    private function storeRules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'how_to_use_en' => 'nullable|string',
            'how_to_use_ar' => 'nullable|string',
            'features_en' => 'nullable|json',
            'features_ar' => 'nullable|json',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
        ];
    }

    private function updateRules(): array
    {
        return [
            'product_id' => 'nullable|exists:products,id',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'how_to_use_en' => 'nullable|string',
            'how_to_use_ar' => 'nullable|string',
            'features_en' => 'nullable|json',
            'features_ar' => 'nullable|json',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
        ];
    }
} 