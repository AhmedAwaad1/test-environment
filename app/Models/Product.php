<?php

namespace App\Models;

use App\Http\Services\GeoCurrency\GeoCurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'sub_category_id',
        'category_id',
        'quantity',
          'is_best_seller',
    'is_new_arrival',
        'is_active',
        'has_variants',
        'sku',
    ];


    protected $casts = [
        'has_variants' => 'boolean',
        'is_active'    => 'boolean',
           'is_best_seller' => 'boolean',
    'is_new_arrival' => 'boolean',
    ];

    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function currentPrice()
    {
        return $this->hasOne(ProductPrice::class)->where('currency_id', config('app.default_currency_id'));
    }


    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function productOptions()
    {
        return $this->hasMany(ProductOption::class)->orderBy('order', 'asc');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function colors()
    {
        return $this->belongsToMany(Color::class, 'product_color');
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function setItems()
    {
        return $this->belongsToMany(ProductSetItems::class, 'product_product_set_item', 'product_id', 'product_set_item_id')
            ->withTimestamps();
    }

    public function lowestPrice()
    {
        return $this->hasOne(ProductPrice::class)->orderByRaw('COALESCE(price_after_discount, price) ASC');
    }



    public function scopeFilter($query, $filters)
    {
        // Search in name and description
        if ($filters['search'] ?? false) {
            $query->where(function($q) use ($filters) {
                $q->where('name_en', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('name_ar', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Category and subcategory filters
        if ($filters['category_id'] ?? false) {
            $query->where('category_id', $filters['category_id']);
        }
        if ($filters['sub_category_id'] ?? false) {
            $query->where('sub_category_id', $filters['sub_category_id']);
        }

        // Special filters
        if (($filters['is_best_seller'] ?? false) || ($filters['best_seller'] ?? false)) {
            $query->where('is_best_seller', 1);
        }
        if (($filters['is_new_arrival'] ?? false) || ($filters['new_arrival'] ?? false)) {
            $query->where('is_new_arrival', 1);
        }

        // Active status filter
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Has variants filter
        if (isset($filters['has_variants'])) {
            $query->where('has_variants', $filters['has_variants']);
        }

        // Stock status filter
        if (isset($filters['in_stock'])) {
            if ($filters['in_stock']) {
                $query->where(function($q) {
                    $q->where('quantity', '>', 0)
                      ->orWhereHas('productVariants', function($q) {
                          $q->where('quantity', '>', 0);
                      });
                });
            } else {
                $query->where(function($q) {
                    $q->where('quantity', '<=', 0)
                      ->orWhereDoesntHave('productVariants', function($q) {
                          $q->where('quantity', '>', 0);
                      });
                });
            }
        }

        // Attribute filters
        if (isset($filters['filters']) && is_array($filters['filters'])) {
            foreach ($filters['filters'] as $filter) {
                $attributeName = $filter['attribute_name'] ?? null;
                $value = $filter['value'] ?? null;

                if ($attributeName && $value) {
                    $normalizedValue = strtolower($value);
                    $query->whereHas('productVariants.optionValues', function ($q) use ($attributeName, $normalizedValue) {
                        $q->whereRaw('LOWER(value) = ?', [$normalizedValue])
                          ->whereHas('productOption.optionType', function ($q2) use ($attributeName) {
                              $q2->whereRaw('LOWER(name) = ?', [strtolower($attributeName)]);
                          });
                    });
                }
            }
        }

//        $prices = DB::table('product_prices')
//                    ->select('product_id', DB::raw('MIN(COALESCE(price_after_discount, price)) as final_price'))
//                    ->groupBy('product_id')
//                    ->get();
//
//        dd($prices);


        if (isset($filters['min_price']) || isset($filters['max_price'])) {
            $currencyId = optional(app(GeoCurrencyService::class)->getCurrencyForRequest())->id;

            if ($currencyId) {
                $query->whereIn('id', function ($subquery) use ($filters, $currencyId) {
                    $subquery->select('product_id')
                             ->from('product_prices')
                             ->where('currency_id', $currencyId)
                             ->groupBy('product_id');

                    if (isset($filters['min_price']) && isset($filters['max_price'])) {
                        $subquery->havingRaw('MIN(COALESCE(price_after_discount, price)) BETWEEN ? AND ?', [
                            $filters['min_price'],
                            $filters['max_price'],
                        ]);
                    } elseif (isset($filters['min_price'])) {
                        $subquery->havingRaw('MIN(COALESCE(price_after_discount, price)) >= ?', [$filters['min_price']]);
                    } elseif (isset($filters['max_price'])) {
                        $subquery->havingRaw('MIN(COALESCE(price_after_discount, price)) <= ?', [$filters['max_price']]);
                    }
                });
            }
        }


        // Sort options
        if ($filters['sort_by'] ?? false) {
            $direction = $filters['sort_direction'] ?? 'asc';
            switch ($filters['sort_by']) {
                case 'name':
                case 'A_Z':
                    $query->orderByRaw('LOWER(name_en) asc');
                    break;
                case 'Z_A':
                    $query->orderByRaw('LOWER(name_en) desc');
                    break;
                case 'created_at':
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'best_seller':
                    $query->orderBy('is_best_seller', 'desc')
                          ->orderBy('created_at', 'desc');
                    break;
                case 'rating':
                    $query->withAvg('reviews', 'rating')
                        ->orderBy('reviews_avg_rating', 'desc')
                        ->orderBy('created_at', 'desc');
                    break;
                case 'lowest_price':
                case 'highest_price':
                    $direction = $filters['sort_by'] === 'highest_price' ? 'desc' : 'asc';
                    $currencyId = optional(app(GeoCurrencyService::class)->getCurrencyForRequest())->id ?? config('app.default_currency_id');

                    $query->select('products.*')
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
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        return $query;
    }
}
