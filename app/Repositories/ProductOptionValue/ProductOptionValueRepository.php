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
        return $this->model->updateOrCreate(
            [
                'product_option_id' => $data['product_option_id'],
                'value'             => $data['value'],
            ],
            $data
        );
    }

    public function delete($id)
    {
        $query = $this->model->where('id', $id)->first();

        if($query) {
            return $query->delete();
        }

        return null;
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
