<?php

namespace App\Repositories\ProductVariant;

use App\Models\ProductVariant;

class ProductVariantRepository
{
    public function getAll($request)
    {
        return ProductVariant::query()
            ->with('product', 'color', 'size', 'images', 'variantSizes')
            ->filter($request);
    }

    public function find($id)
    {
        return ProductVariant::with('product', 'color', 'size', 'images', 'variantSizes')
            ->find($id);
    }

    public function findVariantByProductId($data)
    {
        return $productVariant = ProductVariant::where('product_id', $data['product_id'])
            ->Where('size_id', $data['size_id'])
            ->where('color_id', $data['color_id'])
            ->first();
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
