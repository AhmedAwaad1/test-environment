<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductOptionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'option_type_id' => ['required', 'exists:product_option_types,id'],
            'order' => ['nullable', 'integer', 'min:1'],
            'values' => ['required', 'array'],
            'values.*.value' => ['required', 'string', 'max:255'],
            'values.*.hex_code' => ['nullable', 'string', 'max:7'],
            'values.*.order' => ['nullable', 'integer', 'min:1'],
            'values.*.images' => ['nullable', 'array'],
            'values.*.images.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];
    }
}
