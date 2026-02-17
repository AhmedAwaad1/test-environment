<?php

namespace App\Filters\Product;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class HasVariantsFilter implements Filter
{
    public function apply(Builder $query, $value): Builder
    {
        if ($value === null) {
            return $query;
        }

        return $query->where('has_variants', $value);
    }
}
