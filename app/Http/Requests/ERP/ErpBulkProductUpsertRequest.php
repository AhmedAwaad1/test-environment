<?php

namespace App\Http\Requests\ERP;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ErpBulkProductUpsertRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(collect($this->all())
            ->map(function ($product) {
                if (is_array($product) && array_key_exists('id', $product)) {
                    $product['id'] = (string) $product['id'];
                }

                return $product;
            })
            ->all());
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '*' => ['array'],
            '*.id' => ['required', 'string', 'max:255', 'distinct'],
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
            $externalIds = collect($this->all())
                ->pluck('id')
                ->filter(fn ($externalId) => $externalId !== null && $externalId !== '')
                ->all();
            $existingExternalIds = array_flip(Product::whereIn('external_id', $externalIds)->pluck('external_id')->all());

            foreach ($this->all() as $index => $product) {
                $isNewProduct = is_array($product) && !isset($existingExternalIds[$product['id'] ?? '']);

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
