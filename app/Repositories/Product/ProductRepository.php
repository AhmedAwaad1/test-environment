<?php

namespace App\Repositories\Product;

use App\Models\Product;

class ProductRepository
{
    public function getAll($request)
    {
        return Product::query()
            ->with('productType', 'category', 'images', 'productVariants')
            ->filter($request);
    }

    public function find($id)
    {
        return Product::with('productType', 'category', 'images', 'productVariants')
            ->find($id);
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update($id, array $data)
    {
        $product = Product::find($id);

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
