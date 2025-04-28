<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_en',
        'name_ar',
        'slug',
        'image',
    ];

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
            $this->attributes['image'] = $value->store('product_types', 'public');
        }
    }
    public function getSlugAttribute($value)
    {
        return $value;
    }
}
