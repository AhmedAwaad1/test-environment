<?php

namespace App\Repositories\ProductOption;

use App\Models\ProductOption;
use App\Models\ProductOptionType;
use Illuminate\Support\Facades\DB;

class ProductOptionRepository
{
    public function __construct(
        protected ProductOption $model
    ) {}


    public function create(array $data)
    {
        return $this->model->updateOrCreate(
            [
                'product_id' => $data['product_id'],
                'product_option_type_id' => $data['product_option_type_id'],
            ],
            $data
        );
    }

    public function delete($id)
    {
        $query = $this->model->where('id', $id)->first();

        if ($query) {
            return $query->delete();
        }

        return null;
    }

    public function getByProductId($productId)
    {
        return $this->model
            ->where('product_id', $productId)
            ->with(['values.images', 'optionType'])
            ->orderBy('order')
            ->get();
    }

    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $option = ProductOption::where('id', $id)->first();
        if ($option) {
            $option->update($data);
            return $option;
        }
        return null;
    }
}
