<?php

namespace App\Filters\Product;

use App\Filters\Filter;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use Illuminate\Database\Eloquent\Builder;

class SortFilter implements Filter
{
    public function apply(Builder $query, $value): Builder
    {
        if (!is_array($value) || !isset($value['sort_by'])) {
            return $query->latest();
        }

        $sortBy = $value['sort_by'];
        $direction = $value['sort_direction'] ?? 'asc';

        switch ($sortBy) {
            case 'name':
            case 'A_Z':
                return $query->orderByRaw('LOWER(name_en) asc');
            case 'Z_A':
                return $query->orderByRaw('LOWER(name_en) desc');
            case 'created_at':
            case 'newest':
                return $query->orderBy('created_at', 'desc');
            case 'best_seller':
                return $query->orderBy('is_best_seller', 'desc')
                             ->orderBy('created_at', 'desc');
            case 'rating':
                return $query->withAvg('reviews', 'rating')
                             ->orderBy('reviews_avg_rating', 'desc')
                             ->orderBy('created_at', 'desc');
            case 'lowest_price':
            case 'highest_price':
                $direction = $sortBy === 'highest_price' ? 'desc' : 'asc';
                $currencyId = optional(app(GeoCurrencyService::class)->getCurrencyForRequest())->id ?? config('app.default_currency_id');

                return $query->select('products.*')
                             ->addSelect(['sort_price' => function ($q) use ($currencyId) {
                                 $q->selectRaw('CASE 
                                     WHEN has_variants = 1 AND EXISTS (SELECT 1 FROM product_variants WHERE product_id = products.id) THEN (
                                         SELECT COALESCE(pv.price_after_discount, pv.price) 
                                         FROM product_variants pv 
                                         WHERE pv.product_id = products.id 
                                         ORDER BY pv.order ASC, pv.id ASC 
                                         LIMIT 1
                                     )
                                     ELSE (
                                         SELECT CASE WHEN pp.price_after_discount > 0 THEN pp.price_after_discount ELSE pp.price END 
                                         FROM product_prices pp 
                                         LEFT JOIN currencies c ON c.id = pp.currency_id
                                         WHERE pp.product_id = products.id 
                                         ORDER BY 
                                             CASE WHEN pp.currency_id = ? THEN 0 ELSE 1 END ASC,
                                             CASE WHEN c.is_default = 1 THEN 0 ELSE 1 END ASC,
                                             pp.id ASC
                                         LIMIT 1
                                     )
                                 END', [$currencyId]);
                             }])
                             ->orderBy('sort_price', $direction);
            default:
                return $query->latest();
        }
    }
}
