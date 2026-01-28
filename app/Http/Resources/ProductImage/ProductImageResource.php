<?php

namespace App\Http\Resources\ProductImage;

use App\Http\Resources\Category\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'is_main' => (int) $this->is_main,
            'image' => $this->image, // The original string (Required for old Frontend)
            'thumbnails' => [ // New optimized versions grouped together
                'webp' => $this->image_webp,
                'medium' => $this->image_medium,
                'small' => $this->image_small,
            ]
        ];
    }
}
