<?php

namespace App\Filters\Product;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ActiveFilter implements Filter
{
    public function apply(Builder $query, $value): Builder
    {
        if ($value === null) {
            return $query;
        }

        return $query->where('is_active', $value);
    }
}
