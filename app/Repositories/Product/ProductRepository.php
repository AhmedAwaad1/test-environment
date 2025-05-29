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

    public function getAll($request)
    {
        $query = $this->model->with(['category', 'subCategory', 'images'])->filter($request);

        if ($request->has('per_page')) {
            return $query->paginate($request->per_page);
        }

        return $query->get();
    }

    public function find($id)
    {
        return $this->model
            ->with(['category', 'subCategory', 'productOptions.values', 'productVariants'])
            ->findOrFail($id);
    }

    public function findWithVariants($id)
    {
        $productWithVariants = $this->model
            ->with([
                'category:id,name_en,name_ar',
                'subCategory:id,name_en,name_ar',
                'images' => function($query) {
                },
                'productOptions.values.images',
                'productOptions.values' => function($query) {
                    $query->orderBy('order');
                },
                'productVariants' => function($query) {
                    $query->orderBy('order')->with([
                        'optionValues.productOption',
                        'optionValues.images'
                    ]);
                }
            ])
            ->findOrFail($id);

        return $productWithVariants;
    }


    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $product = $this->model->create([
                'name_en' => $data['name_en'],
                'name_ar' => $data['name_ar'],
                'description_en' => $data['description_en'] ?? null,
                'description_ar' => $data['description_ar'] ?? null,
                'category_id' => $data['category_id'],
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'has_variants' => $data['has_variants'] ?? false,
                'price' => $data['price'] ?? null,
                'price_after_discount' => $data['price_after_discount'] ?? null,
                'quantity' => $data['quantity'] ?? null,
                'sku' => $data['sku'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        try {
            DB::beginTransaction();

            $product = $this->model->findOrFail($id);

            $product->update(array_filter([
                'name_en' => $data['name_en'] ?? null,
                'name_ar' => $data['name_ar'] ?? null,
                'description_en' => $data['description_en'] ?? null,
                'description_ar' => $data['description_ar'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'has_variants' => $data['has_variants'] ?? $product->has_variants,
                'price' => $data['price'] ?? null,
                'price_after_discount' => $data['price_after_discount'] ?? null,
                'quantity' => $data['quantity'] ?? null,
                'sku' => $data['sku'] ?? null,
                'is_active' => $data['is_active'] ?? $product->is_active,
            ]));

            DB::commit();
            return $product->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $product = $this->model->findOrFail($id);

            // Delete all images
            foreach ($product->images as $image) {
                if (!empty($image->image) && Storage::exists($image->image)) {
                    Storage::delete($image->image);
                }
            }

            // Delete variants and their images
            foreach ($product->productVariants as $variant) {
                foreach ($variant->images as $image) {
                    if (!empty($image->image) && Storage::exists($image->image)) {
                        Storage::delete($image->image);
                    }
                }
            }

            $isDeleted = $product->delete();

            DB::commit();
            return $isDeleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
