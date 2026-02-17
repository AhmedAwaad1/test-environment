<?php

namespace App\Filters\Product;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class StockFilter implements Filter
{
    public function apply(Builder $query, $value): Builder
    {
        if ($value === null) {
            return $query;
        }

        if ($value) {
            return $query->where(function ($q) {
                $q->where('quantity', '>', 0)
                  ->orWhereHas('productVariants', function ($q) {
                      $q->where('quantity', '>', 0);
                  });
            });
        }

        return $query->where(function ($q) {
            $q->where('quantity', '<=', 0)
              ->orWhereDoesntHave('productVariants', function ($q) {
                  $q->where('quantity', '>', 0);
              });
        });
    }
}
