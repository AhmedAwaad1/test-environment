<?php

namespace App\Filters\Product;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CategoryFilter implements Filter
{
    public function apply(Builder $query, $value): Builder
    {
        if (!$value) {
            return $query;
        }

        return $query->where('category_id', $value);
    }
}
