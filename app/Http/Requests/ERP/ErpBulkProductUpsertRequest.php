<?php

namespace App\Http\Requests\ERP;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ErpBulkProductUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '*' => ['array'],
            '*.sku' => ['required', 'string', 'max:255', 'distinct'],
            '*.name' => ['nullable', 'string', 'max:255'],
            '*.name2' => ['nullable', 'string', 'max:255'],
            '*.name_ar' => ['nullable', 'string', 'max:255'],
            '*.name_en' => ['nullable', 'string', 'max:255'],
            '*.description' => ['nullable', 'string'],
            '*.description2' => ['nullable', 'string'],
            '*.price' => ['required', 'numeric', 'min:0'],
            '*.stock' => ['nullable', 'integer', 'min:0'],
            '*.quantity' => ['nullable', 'integer', 'min:0'],
            '*.image_url' => ['nullable', 'string', 'url'],
            '*.group_code' => ['nullable', 'integer'],
            '*.group2_code' => ['nullable', 'integer'],
            '*.group3_code' => ['nullable', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $skus = collect($this->all())
                ->pluck('sku')
                ->filter(fn ($sku) => $sku !== null && $sku !== '')
                ->all();
            $existingSkus = array_flip(Product::whereIn('sku', $skus)->pluck('sku')->all());

            foreach ($this->all() as $index => $product) {
                $isNewProduct = is_array($product) && !isset($existingSkus[$product['sku'] ?? '']);

                if ($isNewProduct && !isset($product['name']) && !isset($product['name_ar'])) {
                    $validator->errors()->add("{$index}.name", 'The name or name_ar field is required.');
                }

                if ($isNewProduct && !isset($product['group_code'])) {
                    $validator->errors()->add("{$index}.group_code", 'The group_code field is required when creating a new product.');
                }
            }
        });
    }
}
