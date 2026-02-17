<?php

namespace App\Filters\Product;

use App\Filters\Filter;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use Illuminate\Database\Eloquent\Builder;

class PriceRangeFilter implements Filter
{
    public function apply(Builder $query, $value): Builder
    {
        if (!is_array($value) || (!isset($value['min_price']) && !isset($value['max_price']))) {
            return $query;
        }

        $currencyId = optional(app(GeoCurrencyService::class)->getCurrencyForRequest())->id;

        if (!$currencyId) {
            return $query;
        }

        return $query->whereIn('id', function ($subquery) use ($value, $currencyId) {
            $subquery->select('product_id')
                     ->from('product_prices')
                     ->where('currency_id', $currencyId)
                     ->groupBy('product_id');

            if (isset($value['min_price']) && isset($value['max_price'])) {
                $subquery->havingRaw('MIN(COALESCE(price_after_discount, price)) BETWEEN ? AND ?', [
                    $value['min_price'],
                    $value['max_price'],
                ]);
            } elseif (isset($value['min_price'])) {
                $subquery->havingRaw('MIN(COALESCE(price_after_discount, price)) >= ?', [$value['min_price']]);
            } elseif (isset($value['max_price'])) {
                $subquery->havingRaw('MIN(COALESCE(price_after_discount, price)) <= ?', [$value['max_price']]);
            }
        });
    }
}
