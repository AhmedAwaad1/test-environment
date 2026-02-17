<?php

namespace App\Models;

use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Filters\Product\ProductFilterPipeline;
use Illuminate\Database\Eloquent\Builder;
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
        return app(ProductFilterPipeline::class)->apply($query, $filters);
    }
}
