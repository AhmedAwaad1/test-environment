<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Blog extends Model
{
    use HasFactory;
    public $fillable = [
        'title_en',
        'title_ar',
        'slug',
        'description_en',
        'description_ar',
        'content_en',
        'content_ar',
        'published_at',
        'image',
        'is_active'
    ];

    public function getImageAttribute($value)
    {
        return $value ? url(Storage::url($value)) : null;
    }
}
