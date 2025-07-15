<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'currency_id',
        'price',
        'price_after_discount',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function scopeFilter($query, $filters)
    {
        if ($filters['product_id'] ?? false) {
            $query->where('product_id', $filters['product_id']);
        }

        if ($filters['currency_id'] ?? false) {
            $query->where('currency_id', $filters['currency_id']);
        }

        if ($filters['sort_by'] ?? false) {
            $direction = $filters['sort_direction'] ?? 'asc';
            switch ($filters['sort_by']) {
                case 'price':
                    $query->orderBy('price', $direction);
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
