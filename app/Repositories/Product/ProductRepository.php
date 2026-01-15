<?php

namespace App\Repositories\Product;

use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\VariantOptionValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductRepository
{
    public function __construct(
        protected Product $model,
        protected ProductOption $productOption,
        protected ProductOptionValue $productOptionValue,
        protected ProductVariant $productVariant,
        protected VariantOptionValue $variantOptionValue
    ) {}

    /**
     * Get all products with optional filters
     */
    public function getAll($request, array $filters = [])
    {
        $query = $this->model
            ->with([
                'category', 
                'subCategory', 
                'images', 
                'productPrices.currency',
                'productVariants.optionValues.productOption.optionType'
            ])
            ->filter($filters);

        return $request->filled('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();
    }

    public function find($id)
    {
        return $this->model
            ->with([
                'category',
                'subCategory',
                'productOptions.values',
                'productVariants.optionValues',
                'productPrices.currency'
            ])
            ->findOrFail($id);
    }

    public function findWithVariants($id)
    {
        return $this->model
            ->with([
                'category:id,name_en,name_ar',
                'subCategory:id,name_en,name_ar',
                'productPrices.currency',
                'images',
                'productOptions.values.images',
                'productOptions.values' => fn ($q) => $q->orderBy('order'),
                'productVariants' => fn ($q) =>
                    $q->orderBy('order')->with([
                        'optionValues.productOption',
                        'optionValues.images',
                        'images'
                    ])
            ])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->model->create([
                'name_en' => $data['name_en'],
                'name_ar' => $data['name_ar'],
                'description_en' => $data['description_en'] ?? null,
                'description_ar' => $data['description_ar'] ?? null,
                'category_id' => $data['category_id'],
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'has_variants' => $data['has_variants'] ?? false,
                'quantity' => $data['quantity'] ?? null,
                'sku' => $data['sku'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_best_seller' => $data['is_best_seller'] ?? false,
                'is_new_arrival' => $data['is_new_arrival'] ?? false,
            ]);
        });
    }

public function update($id, array $data)
{
    return DB::transaction(function () use ($id, $data) {
        $product = $this->model->findOrFail($id);

        // FIX: Do NOT use array_filter() alone. 
        // Either remove it entirely or use a callback to only filter NULLS.
        $updateData = [
            'name_en' => $data['name_en'] ?? $product->name_en,
            'name_ar' => $data['name_ar'] ?? $product->name_ar,
            'description_en' => $data['description_en'] ?? $product->description_en,
            'description_ar' => $data['description_ar'] ?? $product->description_ar,
            'category_id' => $data['category_id'] ?? $product->category_id,
            'sub_category_id' => $data['sub_category_id'] ?? $product->sub_category_id,
            'has_variants' => isset($data['has_variants']) ? $data['has_variants'] : $product->has_variants,
            'quantity' => $data['quantity'] ?? $product->quantity,
            'sku' => $data['sku'] ?? $product->sku,
            'is_active' => isset($data['is_active']) ? $data['is_active'] : $product->is_active,
            'is_best_seller' => isset($data['is_best_seller']) ? $data['is_best_seller'] : $product->is_best_seller,
            'is_new_arrival' => isset($data['is_new_arrival']) ? $data['is_new_arrival'] : $product->is_new_arrival,
        ];

        $product->update($updateData);

        return $product->fresh();
    });
}

    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            $product = $this->model
                ->with(['images', 'productOptions.values.images', 'productVariants', 'productPrices'])
                ->findOrFail($id);

            $disk = Storage::disk('public');

            // 1. Delete product images from storage
            foreach ($product->images as $image) {
                if ($image->image && $disk->exists($image->image)) {
                    $disk->delete($image->image);
                }
            }

            // 2. Delete product option value images from storage
            foreach ($product->productOptions as $option) {
                foreach ($option->values as $value) {
                    foreach ($value->images as $image) {
                        if ($image->image && $disk->exists($image->image)) {
                            $disk->delete($image->image);
                        }
                    }
                    // Explicitly delete option value to trigger any potential events
                    $value->delete();
                }
                // Explicitly delete option
                $option->delete();
            }

            // 3. Explicitly delete variants to ensure cleanup if DB cascade fails
            foreach ($product->productVariants as $variant) {
                // If variant has its own cleanup logic (like in ProductVariantRepository::delete),
                // we should ideally call that. But for now, we'll just delete the record.
                $variant->delete();
            }

            // 4. Explicitly delete prices
            foreach ($product->productPrices as $price) {
                $price->delete();
            }

            // 5. Finally delete the product itself
            return $product->delete();
        });
    }

    public function updateProductPrices(Product $product, array $prices): void
    {
        $product->productPrices()->delete();

        foreach ($prices as $price) {
            $product->productPrices()->create([
                'currency_id' => $price['currency_id'],
                'price' => $price['price'],
                'price_after_discount' => $price['price_after_discount'] ?? null,
            ]);
        }
    }

    public function createProductPrices(Product $product, array $prices): void
    {
        foreach ($prices as $price) {
            $product->productPrices()->create([
                'currency_id' => $price['currency_id'],
                'price' => $price['price'],
                'price_after_discount' => $price['price_after_discount'] ?? null,
            ]);
        }
    }
}
