<?php

namespace App\Repositories\ERP;

use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPrice;
use App\Models\OrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ErpProductRepository
{
    public function paginate(int $perPage): LengthAwarePaginator
    {
        return Product::with(['productPrices.currency', 'images', 'category', 'subCategory', 'subSubCategory'])
            ->whereNotNull('sku')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::with(['productPrices.currency', 'images', 'category', 'subCategory', 'subSubCategory'])
            ->where('sku', $sku)
            ->first();
    }

    public function upsertProduct(array $data): Product
    {
        $product = Product::where('sku', $data['sku'])->first();

        $attributes = [
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? $data['name_ar'],
            'description_ar' => $data['description_ar'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'sub_category_id' => $data['sub_category_id'] ?? null,
            'sub_sub_category_id' => $data['sub_sub_category_id'] ?? null,
            'erp_group_code' => $data['erp_group_code'] ?? null,
            'erp_group2_code' => $data['erp_group2_code'] ?? null,
            'erp_group3_code' => $data['erp_group3_code'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
            'has_variants' => 0,
            'is_active' => 1,
        ];

        if ($product) {
            $product->update($attributes);
        } else {
            $attributes['sku'] = $data['sku'];
            $product = Product::create($attributes);
        }

        if (isset($data['price'])) {
            $this->syncProductPrice($product, (float) $data['price']);
        }

        if (!empty($data['image_url'])) {
            $this->syncProductImage($product, $data['image_url']);
        }

        return $this->findBySku($product->sku);
    }

    public function createProduct(array $data): Product
    {
        $product = Product::create([
            'sku' => $data['sku'],
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? $data['name_ar'],
            'description_ar' => $data['description_ar'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'sub_category_id' => $data['sub_category_id'] ?? null,
            'sub_sub_category_id' => $data['sub_sub_category_id'] ?? null,
            'erp_group_code' => $data['erp_group_code'] ?? null,
            'erp_group2_code' => $data['erp_group2_code'] ?? null,
            'erp_group3_code' => $data['erp_group3_code'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
            'has_variants' => 0,
            'is_active' => 1,
        ]);

        $this->syncProductPrice($product, (float) $data['price']);

        if (!empty($data['image_url'])) {
            $this->syncProductImage($product, $data['image_url']);
        }

        return $this->findBySku($product->sku);
    }

    public function updatePriceAndStock(Product $product, array $data): Product
    {
        if (array_key_exists('stock', $data)) {
            $product->update(['quantity' => (int) $data['stock']]);
        }

        if (array_key_exists('price', $data)) {
            $this->syncProductPrice($product, (float) $data['price']);
        }

        return $this->findBySku($product->sku);
    }

    public function updateProductBySku(Product $product, array $data): Product
    {
        $updateFields = [];

        if (isset($data['name'])) {
            $updateFields['name_ar'] = $data['name'];
        }
        if (isset($data['name2'])) {
            $updateFields['name_en'] = $data['name2'];
        }
        if (isset($data['description'])) {
            $updateFields['description_ar'] = $data['description'];
        }
        if (isset($data['description2'])) {
            $updateFields['description_en'] = $data['description2'];
        }
        if (isset($data['stock'])) {
            $updateFields['quantity'] = (int) $data['stock'];
        }
        if (array_key_exists('category_id', $data)) {
            $updateFields['category_id'] = $data['category_id'];
        }
        if (array_key_exists('sub_category_id', $data)) {
            $updateFields['sub_category_id'] = $data['sub_category_id'];
        }
        if (array_key_exists('sub_sub_category_id', $data)) {
            $updateFields['sub_sub_category_id'] = $data['sub_sub_category_id'];
        }
        if (array_key_exists('erp_group_code', $data)) {
            $updateFields['erp_group_code'] = $data['erp_group_code'];
        }
        if (array_key_exists('erp_group2_code', $data)) {
            $updateFields['erp_group2_code'] = $data['erp_group2_code'];
        }
        if (array_key_exists('erp_group3_code', $data)) {
            $updateFields['erp_group3_code'] = $data['erp_group3_code'];
        }

        if (!empty($updateFields)) {
            $product->update($updateFields);
        }

        if (isset($data['price'])) {
            $this->syncProductPrice($product, (float) $data['price']);
        }

        if (!empty($data['image_url'])) {
            $this->syncProductImage($product, $data['image_url']);
        }

        return $this->findBySku($product->sku);
    }

    public function syncProductPrice(Product $product, float $price): ProductPrice
    {
        $currency = Currency::where('is_default', 1)->first()
            ?? Currency::first();

        $currencyId = $currency?->id ?? 1;

        return ProductPrice::updateOrCreate(
            [
                'product_id' => $product->id,
                'currency_id' => $currencyId,
            ],
            [
                'price' => $price,
            ]
        );
    }

    public function syncProductImage(Product $product, string $imageUrl): ProductImage
    {
        $mainImage = ProductImage::where('product_id', $product->id)
            ->where('is_main', true)
            ->first();

        if ($mainImage) {
            $mainImage->update([
                'image' => $imageUrl,
            ]);
            return $mainImage;
        }

        return ProductImage::create([
            'product_id' => $product->id,
            'image' => $imageUrl,
            'is_main' => true,
        ]);
    }

    public function hasCommercialReferences(Product $product): bool
    {
        return OrderItem::where('product_id', $product->id)->exists();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
