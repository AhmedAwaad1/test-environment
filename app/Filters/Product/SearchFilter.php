<?php

namespace App\Filters\Product;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class SearchFilter implements Filter
{
    public function apply(Builder $query, $value): Builder
    {
        if (!$value) {
            return $query;
        }

        return $query->where(function ($q) use ($value) {
            $q->where('name_en', 'like', '%' . $value . '%')
              ->orWhere('name_ar', 'like', '%' . $value . '%');
        });
    }
}
