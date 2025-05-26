<?php

namespace App\Repositories\ProductOption;

use App\Models\ProductOption;
use Illuminate\Support\Facades\DB;

class ProductOptionRepository
{
    public function __construct(
        protected ProductOption $model
    ) {}

    public function create(array $data)
    {
        return $this->model->create([
            'product_id' => $data['product_id'],
            'name' => $data['name'],
            'order' => $data['order'] ?? 1,
        ]);
    }

    public function createWithValues($productId, array $data)
    {
        try {
            DB::beginTransaction();

            $option = $this->create([
                'product_id' => $productId,
                'name' => $data['name'],
                'order' => $data['order'] ?? 1,
            ]);

            if (isset($data['values'])) {
                foreach ($data['values'] as $valueIndex => $value) {
                    $option->productOptionValues()->create([
                        'value' => $value,
                        'order' => $valueIndex + 1,
                    ]);
                }
            }

            DB::commit();
            return $option;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getByProductId($productId)
    {
        return $this->model
            ->where('product_id', $productId)
            ->with('productOptionValues')
            ->orderBy('order')
            ->get();
    }

    public function update($id, array $data)
    {
        $option = ProductOption::find($id);
        if ($option) {
            $option->update($data);
            return $option;
        }
        return null;
    }
}
