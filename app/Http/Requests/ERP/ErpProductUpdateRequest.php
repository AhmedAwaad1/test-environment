<?php

namespace App\Http\Requests\ERP;

use Illuminate\Foundation\Http\FormRequest;

class ErpProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'name2' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description2' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'image_url' => ['nullable', 'string', 'url'],
            'group_code' => ['nullable', 'integer'],
            'group2_code' => ['nullable', 'integer'],
            'group3_code' => ['nullable', 'integer'],
        ];
    }
}
