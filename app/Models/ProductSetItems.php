<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSetItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'sku',
        'quantity',
        'is_active',
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'image',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
    ];

    /**
     * Many-to-many relationship with Products
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_product_set_item', 'product_set_item_id', 'product_id')
            ->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function productSetItemPrices()
    {
        return $this->hasMany(ProductSetItemPrice::class, 'product_set_item_id');
    }

    /**
     * Keep old relationship for backward compatibility (if needed)
     * @deprecated Use products() instead
     */
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    /**
     * Calculate total price for a specific currency from all products
     */
    public function getTotalPriceForCurrency($currencyId, ?array $selectedProductIds = null)
    {
        $totalPrice = 0;
        $totalPriceAfterDiscount = 0;

        $products = $this->products;
        if ($selectedProductIds) {
            $products = $products->whereIn('id', $selectedProductIds);
        }

        foreach ($products as $product) {
            $productPrice = $product->productPrices()->where('currency_id', $currencyId)->first();
            if ($productPrice) {
                $totalPrice += $productPrice->price;
                $totalPriceAfterDiscount += ($productPrice->price_after_discount ?? $productPrice->price);
            }
        }

        return [
            'price' => $totalPrice,
            'price_after_discount' => $totalPriceAfterDiscount,
        ];
    }

    /**
     * Get all calculated prices grouped by currency
     */
    public function getCalculatedPrices()
    {
        $pricesByCurrency = [];

        foreach ($this->products as $product) {
            foreach ($product->productPrices as $productPrice) {
                $currencyId = $productPrice->currency_id;
                
                if (!isset($pricesByCurrency[$currencyId])) {
                    $pricesByCurrency[$currencyId] = [
                        'currency_id' => $currencyId,
                        'currency' => $productPrice->currency,
                        'price' => 0,
                        'price_after_discount' => 0,
                    ];
                }

                $pricesByCurrency[$currencyId]['price'] += $productPrice->price;
                $pricesByCurrency[$currencyId]['price_after_discount'] += ($productPrice->price_after_discount ?? $productPrice->price);
            }
        }

        return array_values($pricesByCurrency);
    }

    public function scopeFilter($query, $filters)
    {
        // Category ID filter
        if ($filters['category_id'] ?? false) {
            $query->where('category_id', $filters['category_id']);
        }

        // Sub Category ID filter
        if ($filters['sub_category_id'] ?? false) {
            $query->where('sub_category_id', $filters['sub_category_id']);
        }

        // Search in name and description
        if ($filters['search'] ?? false) {
            $query->where(function($q) use ($filters) {
                $q->where('name_en', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('name_ar', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Product ID filter - search in related products
        if ($filters['product_id'] ?? false) {
            $query->whereHas('products', function($q) use ($filters) {
                $q->where('products.id', $filters['product_id']);
            });
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

    public function getImageAttribute($value)
    {
        if ($value) {
            return asset('storage/' . $value);
        }
        return null;
    }

    public function setImageAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['image'] = null;
        } elseif (is_string($value)) {
            $this->attributes['image'] = $value;
        } elseif (is_object($value) && method_exists($value, 'store')) {
            $this->attributes['image'] = $value->store('product_set_items', 'public');
        } else {
            $this->attributes['image'] = null;
        }
    }
} 