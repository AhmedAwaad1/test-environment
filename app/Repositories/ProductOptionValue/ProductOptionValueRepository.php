<?php

namespace App\Repositories\ProductOptionValue;

use App\Models\ProductOptionValue;

class ProductOptionValueRepository
{
    public function __construct(
        protected ProductOptionValue $model
    ) {}

    public function create(array $data)
    {
        return $this->model->create([
            'product_option_id' => $data['product_option_id'],
            'value' => $data['value'],
            'order' => $data['order'] ?? 1,
            'hex_code' => $data['hex_code'] ?? null,
        ]);
    }

    public function delete($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getByOptionId($optionId)
    {
        return $this->model
            ->where('product_option_id', $optionId)
            ->orderBy('order')
            ->get();
    }

    public function update($id, array $data)
    {
        $optionValue = $this->model->find($id);
        if ($optionValue) {
            $optionValue->update($data);
            return $optionValue;
        }
        return null;
    }
}
