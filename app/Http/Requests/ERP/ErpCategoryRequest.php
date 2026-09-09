<?php

namespace App\Http\Requests\ERP;

use Illuminate\Foundation\Http\FormRequest;

class ErpCategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $payload = $this->all();

        $this->replace([
            'categories' => array_is_list($payload) ? $payload : ($payload['categories'] ?? [$payload]),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categories' => ['required', 'array', 'min:1'],
            'categories.*.code' => ['required', 'integer', 'min:1'],
            'categories.*.name' => ['required', 'string', 'max:255'],
            'categories.*.ucode1' => ['required', 'integer', 'min:0'],
            'categories.*.ucode2' => ['required', 'integer', 'min:0'],
            'categories.*.notes' => ['nullable', 'string'],
        ];
    }
}
