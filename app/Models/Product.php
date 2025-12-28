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
        if ($filters['is_best_seller'] ?? false) {
            $query->where('is_best_seller', 1);
        }
        if ($filters['is_new_arrival'] ?? false) {
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
                    $query->orderBy('name_en', $direction);
                    break;
                case 'created_at':
                    $query->orderBy('created_at', $direction);
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
