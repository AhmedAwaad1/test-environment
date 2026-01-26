<?php

namespace App\Repositories\ProductAttributeFilter;

use App\Models\Product;
use App\Models\ProductOptionType;

class ProductAttributeFilterRepository
{
    public function getActiveFilters($categoryId = null)
    {
        $query = ProductOptionType::query();

        if ($categoryId) {
            $query->whereHas('productOptions.product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        return $query->with([
            'productOptions' => function ($q) use ($categoryId) {
                if ($categoryId) {
                    $q->whereHas('product', function ($q2) use ($categoryId) {
                        $q2->where('category_id', $categoryId);
                    });
                }
            },
            'productOptions.values' => function ($query) {
                $query->whereHas('productVariants')
                    ->select('product_option_values.*')
                    ->distinct();
            }
        ])->get();
    }

    /**
     * Filter products by attribute name and value(s)
     * 
     * @param array $filters Array of filters: [['attribute_name' => 'Size', 'value' => 'Earthenware'], ...]
     * @param int|null $perPage
     */
    public function filterProducts(array $filters, $perPage = null)
    {
        $query = Product::with([
            'category',
            'subCategory',
            'images',
            'productPrices.currency',
            'productVariants.optionValues.productOption.optionType'
        ]);

        foreach ($filters as $filter) {
            $attributeName = $filter['attribute_name'] ?? null;
            $value = $filter['value'] ?? null;

            if (!$attributeName || !$value) {
                continue;
            }

            // Normalize value for case-insensitive comparison
            $normalizedValue = strtolower($value);

            $query->whereHas('productVariants.optionValues', function ($q) use ($attributeName, $normalizedValue) {
                $q->whereRaw('LOWER(value) = ?', [$normalizedValue])
                  ->whereHas('productOption.optionType', function ($q2) use ($attributeName) {
                      $q2->whereRaw('LOWER(name) = ?', [strtolower($attributeName)]);
                  });
            });
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }
}
