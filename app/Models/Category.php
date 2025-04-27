<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_ar',
        'slug',
        'image',
    ];

    // public function products()
    // {
    //     return $this->hasMany(Product::class);
    // }
    public function getImageAttribute($value)
    {
        return asset('storage/' . $value);
    }
    public function setImageAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['image'] = $value;
        } else {
            $this->attributes['image'] = $value->store('categories', 'public');
        }
    }
    public function getSlugAttribute($value)
    {
        return $value;
    }
}
