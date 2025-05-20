<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantSize extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_variant_id',
        'size_id',
        'quantity',
    ];

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

}
