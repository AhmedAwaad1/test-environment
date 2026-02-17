<?php

namespace App\Filters\Product;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class AttributeFilter implements Filter
{
    public function apply(Builder $query, $value): Builder
    {
        if (!is_array($value)) {
            return $query;
        }

        foreach ($value as $filter) {
            $attributeName = $filter['attribute_name'] ?? null;
            $val = $filter['value'] ?? null;

            if ($attributeName && $val) {
                $normalizedValue = strtolower($val);
                $query->whereHas('productVariants.optionValues', function ($q) use ($attributeName, $normalizedValue) {
                    $q->whereRaw('LOWER(value) = ?', [$normalizedValue])
                      ->whereHas('productOption.optionType', function ($q2) use ($attributeName) {
                          $q2->whereRaw('LOWER(name) = ?', [strtolower($attributeName)]);
                      });
                });
            }
        }

        return $query;
    }
}
