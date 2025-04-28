<?php

namespace App\Repositories\Product;

use App\Models\Product;

class ProductRepository
{
    public function getAll($request)
    {
        return Product::query()
            ->with('productType', 'category', 'images')
            ->filter($request);
    }

    public function find($id)
    {
        return Product::with('productType', 'category', 'images')
            ->find($id);
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update($id, array $data)
    {
        $product = $this->find($id);

        if ($product) {
            $product->update($data);
        }
        return $product;
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
