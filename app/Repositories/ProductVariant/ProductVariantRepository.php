<?php

namespace App\Repositories\ProductVariant;

use App\Models\ProductVariant;
use App\Models\VariantOptionValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductVariantRepository
{
    public function __construct(
        protected ProductVariant $model,
        protected VariantOptionValue $variantOptionValue
    ) {}

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function findByOptions($productId, array $optionValues)
    {
        $query = $this->model
            ->where('product_id', $productId)
            ->with([
                'optionValues' => function($q) {
                    $q->with(['productOption.optionType', 'images']);
                }
            ]);

        // Count how many option values we're looking for
        $optionValuesCount = count($optionValues);

        // Find variant that has exactly these option values (no more, no less)
        $query->whereHas('optionValues', function ($q) use ($optionValues) {
            $q->whereIn('product_option_values.id', collect($optionValues)->pluck('id'));
        }, '=', $optionValuesCount);

        // Also ensure the variant doesn't have any other option values
        $query->has('optionValues', '=', $optionValuesCount);

        return $query->first();
    }

    public function delete($id)
    {
        $variant = $this->model->findOrFail($id);

        // Delete variant images
        if(!empty($variant->image) && Storage::exists($variant->image)) {
            foreach ($variant->images as $image) {
                if (!empty($image->image) && Storage::exists($image->image)) {
                    Storage::delete($image->image);
                }
                $image->delete();
            }
        }

        $variant->optionValues()->delete();
        return $variant->delete();
    }

    public function find($id)
    {
        return $this->model
            ->with(['optionValues.productOption', 'optionValues.images'])
            ->find($id);
    }

    public function getByProductId($productId)
    {
        return $this->model
            ->with(['optionValues.productOption', 'optionValues.images'])
            ->where('product_id', $productId)
            ->orderBy('order')
            ->get();
    }

    public function update($id, array $data)
    {
        $variant = $this->model->findOrFail($id);
        $variant->update($data);
        return $variant->fresh();
    }

    public function toggleStatus($id)
    {
        $variant = $this->model->findOrFail($id);
        $variant->update(['is_active' => !$variant->is_active]);
        return $variant->fresh();
    }

    public function createProductVariants($product, array $variants, array $optionValueMap)
    {
        foreach ($variants as $index => $variantData) {
            $variant = $this->create([
                'product_id' => $product->id,
                'sku' => $variantData['sku'],
                'price' => $variantData['price'],
                'price_after_discount' => $variantData['price_after_discount'] ?? null,
                'quantity' => $variantData['quantity'],
                'barcode' => $variantData['barcode'] ?? null,
                'weight' => $variantData['weight'] ?? null,
                'order' => $index + 1,
                'is_active' => true,
            ]);

            $this->linkVariantToOptionValues($variant, $variantData['option_values'], $optionValueMap);
        }
    }

    private function linkVariantToOptionValues($variant, array $optionValues, array $optionValueMap)
    {
        $optionValueIds = [];
        foreach ($optionValues as $optionName => $optionValue) {
            if (isset($optionValueMap[$optionName][$optionValue])) {
                $optionValueIds[] = $optionValueMap[$optionName][$optionValue];
            }
        }

        if (!empty($optionValueIds)) {
            $variant->optionValues()->attach($optionValueIds);
        }
    }
}
