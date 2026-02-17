<?php

namespace App\Filters\Product;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class BestSellerFilter implements Filter
{
    public function apply(Builder $query, $value): Builder
    {
        if (!$value) {
            return $query;
        }

        return $query->where('is_best_seller', 1);
    }
}
