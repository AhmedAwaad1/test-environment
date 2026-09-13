<?php

namespace App\Http\Requests\ERP;

use Illuminate\Foundation\Http\FormRequest;

class ErpProductStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('id')) {
            $this->merge(['id' => (string) $this->input('id')]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:255', 'unique:products,external_id'],
            'name' => ['required', 'string', 'max:255'],
            'name2' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description2' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'image_url' => ['nullable', 'string', 'url'],
            'group_code' => ['required', 'integer'],
            'group2_code' => ['nullable', 'integer'],
            'group3_code' => ['nullable', 'integer'],
        ];
    }
}
