<?php

namespace App\Repositories\ProductVariantOptionValue;

use App\Models\VariantOptionValue;
use Illuminate\Support\Facades\DB;

class ProductVariantOptionValueRepository
{
    public function __construct(
        protected VariantOptionValue $model
    ) {}

    public function create(array $data)
    {
        return $this->model->create([
            'product_id' => $data['product_id'],
            'product_option_type_id' => $data['product_option_type_id'],
            'order' => $data['order'] ?? 1,
        ]);
    }

    public function create(array $data)
    {
        return ProductVariantOptionValue::create($data);
    }


    public function delete($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getByProductId($productId)
    {
        return $this->model
            ->where('product_id', $productId)
            ->with(['values', 'optionType'])
            ->orderBy('order')
            ->get();
    }

    public function update($id, array $data)
    {
        $option = ProductVariantOptionValue::find($id);
        if ($option) {
            $option->update($data);
            return $option;
        }
        return null;
    }
}
