<?php

namespace App\Filters\Product;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class NewArrivalFilter implements Filter
{
    public function apply(Builder $query, $value): Builder
    {
        if (!$value) {
            return $query;
        }

        return $query->where('is_new_arrival', 1);
    }
}
