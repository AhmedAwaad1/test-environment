<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOptionType extends Model
{
    protected $fillable = [
        'name',
    ];

    public function productOptions(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }
}
