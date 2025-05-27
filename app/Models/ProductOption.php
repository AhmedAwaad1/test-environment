<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_option_type_id',
        'order',
    ];
    protected $casts = [
        'order' => 'integer',
    ];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function optionType(): BelongsTo
    {
        return $this->belongsTo(ProductOptionType::class, 'product_option_type_id');
    }
    public function values(): HasMany
    {
        return $this->hasMany(ProductOptionValue::class);
    }
}
