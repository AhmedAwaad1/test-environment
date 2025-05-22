<?php

namespace App\Repositories\ProductVariant;

use App\Models\ProductVariant;

class ProductVariantRepository
{
    public function getAll($request)
    {
        return ProductVariant::query()
            ->with('product', 'color', 'size', 'images')
            ->filter($request);
    }

    public function find($id)
    {
        return ProductVariant::with('product', 'color', 'size', 'images')
            ->orderBy('size_id', 'asc')
            ->find($id);
    }

    public function findVariantByProductId($data)
    {
        return ProductVariant::where('product_id', $data['product_id'])
            ->when(isset($data['size_id']), function ($query) use ($data) {
                $query->where('size_id', $data['size_id']);
            })
            ->when(isset($data['color_id']), function ($query) use ($data) {
                $query->where('color_id', $data['color_id']);
            })
            ->first();
    }

    public function create(array $data)
    {
        $createdVariants = [];

        foreach ($data['sizes'] as $sizeData) {
            $variant = ProductVariant::create([
                'product_id' => $data['product_id'],
                'color_id' => $data['color_id'],
                'size_id' => $sizeData['size_id'],
                'price' => $data['price'],
                'price_after_discount' => $data['price_after_discount'] ?? null,
                'quantity' => $sizeData['quantity'],
                'sku' => $data['sku'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $createdVariants[] = $variant;
        }

        return $createdVariants;
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
