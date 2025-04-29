<?php

namespace App\Repositories\ProductVariant;

use App\Models\ProductVariant;

class ProductVariantRepository
{
    public function getAll($request)
    {
        return ProductVariant::query()
            ->with('product', 'color', 'size')
            ->filter($request);
    }

    public function find($id)
    {
        return ProductVariant::with('product', 'color', 'size')
            ->find($id);
    }

    public function create(array $data)
    {
        return ProductVariant::create($data);
    }

    public function update($id, array $data)
    {
        $product = ProductVariant::find($id);

        if ($product) {
            $product->update($data);
        }
    }

    public function delete($id)
    {
        $product = $this->find($id);
        if ($product) {
            $product->delete();
        }
        return $product;
    }
}
