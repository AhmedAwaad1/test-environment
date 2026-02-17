<?php

namespace App\Filters\Product;

use App\Filters\FilterPipeline;
use Illuminate\Database\Eloquent\Builder;

class ProductFilterPipeline extends FilterPipeline
{
    protected array $filters = [
        'search'           => SearchFilter::class,
        'category_id'      => CategoryFilter::class,
        'sub_category_id'  => SubCategoryFilter::class,
        'is_best_seller'   => BestSellerFilter::class,
        'best_seller'      => BestSellerFilter::class,
        'is_new_arrival'   => NewArrivalFilter::class,
        'new_arrival'      => NewArrivalFilter::class,
        'is_active'        => ActiveFilter::class,
        'has_variants'     => HasVariantsFilter::class,
        'in_stock'         => StockFilter::class,
        'filters'          => AttributeFilter::class,
        'price_range'      => PriceRangeFilter::class,
        'sort'             => SortFilter::class,
    ];

    /**
     * Apply the product filters.
     *
     * @param Builder $query
     * @param array $input
     * @return Builder
     */
    public function apply(Builder $query, array $input): Builder
    {
        $otherFilters = $this->filters;
        unset($otherFilters['sort']);
        unset($otherFilters['price_range']);

        foreach ($otherFilters as $key => $filterClass) {
            $value = $this->resolveValue($key, $input);
            if ($value !== null) {
                $query = app($filterClass)->apply($query, $value);
            }
        }

        // Apply Price Range
        if (isset($this->filters['price_range'])) {
            $value = $this->resolveValue(['min_price', 'max_price'], $input);
            if ($value) {
                $query = app($this->filters['price_range'])->apply($query, $value);
            }
        }

        // Apply Sort
        if (isset($this->filters['sort'])) {
            $value = $this->resolveValue(['sort_by', 'sort_direction'], $input);
            $query = app($this->filters['sort'])->apply($query, $value);
        }

        return $query;
    }
}
