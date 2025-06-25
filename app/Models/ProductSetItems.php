<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSetItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'how_to_use_en',
        'how_to_use_ar',
        'features_en',
        'features_ar',
        'image',
    ];

    protected $casts = [
        'features_en' => 'array',
        'features_ar' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
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

        // Product ID filter
        if ($filters['product_id'] ?? false) {
            $query->where('product_id', $filters['product_id']);
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
        if (is_string($value)) {
            $this->attributes['image'] = $value;
        } else {
            $this->attributes['image'] = $value->store('product_set_items', 'public');
        }
    }
} 