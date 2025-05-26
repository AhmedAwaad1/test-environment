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
        return $this->model->create([
            'product_id' => $data['product_id'],
            'sku' => $data['sku'],
            'price' => $data['price'],
            'price_after_discount' => $data['price_after_discount'] ?? null,
            'quantity' => $data['quantity'],
            'barcode' => $data['barcode'] ?? null,
            'weight' => $data['weight'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'order' => $data['order'] ?? 1,
        ]);
    }

    public function createWithOptions($productId, array $data)
    {
        try {
            DB::beginTransaction();

            // Create variant
            $variant = $this->create([
                'product_id' => $productId,
                'sku' => $data['sku'],
                'price' => $data['price'],
                'price_after_discount' => $data['price_after_discount'] ?? null,
                'quantity' => $data['quantity'],
                'barcode' => $data['barcode'] ?? null,
                'weight' => $data['weight'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'order' => $data['order'] ?? 1,
            ]);

            // Link variant with option values
            foreach ($data['option_values'] as $optionValue) {
                $this->variantOptionValue->create([
                    'product_variant_id' => $variant->id,
                    'product_option_value_id' => $optionValue['option_value_id'],
                ]);
            }

            // Handle images if this is a color variant
            if (isset($data['images'])) {
                foreach ($data['images'] as $image) {
                    $variant->images()->create([
                        'image' => $image['path'],
                        'is_main' => $image['is_main'] ?? false,
                    ]);
                }
            }

            DB::commit();
            return $variant;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findByOptions($productId, array $selectedOptions)
    {
        $query = $this->model
            ->where('product_id', $productId)
            ->with(['optionValues', 'images']);

        foreach ($selectedOptions as $optionId => $valueId) {
            $query->whereHas('optionValues', function ($q) use ($optionId, $valueId) {
                $q->where('product_option_id', $optionId)
                  ->where('id', $valueId);
            });
        }

        return $query->first();
    }

    public function delete($id)
    {
        $variant = $this->model->findOrFail($id);

        // Delete variant images
        foreach ($variant->images as $image) {
            if (!empty($image->image) && Storage::exists($image->image)) {
                Storage::delete($image->image);
            }
            $image->delete();
        }

        $variant->optionValues()->delete();
        return $variant->delete();
    }

    public function find($id)
    {
        return ProductVariant::with(['optionValues.option', 'product'])->find($id);
    }

    public function getByProductId($productId)
    {
        return ProductVariant::with(['optionValues.option'])
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->orderBy('position')
            ->get();
    }

    public function update($id, array $data)
    {
        $variant = ProductVariant::find($id);
        if ($variant) {
            $variant->update($data);
            return $variant;
        }
        return null;
    }

    public function toggleStatus($id)
    {
        $variant = ProductVariant::find($id);
        if ($variant) {
            $variant->is_active = !$variant->is_active;
            $variant->save();
            return $variant;
        }
        return null;
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
