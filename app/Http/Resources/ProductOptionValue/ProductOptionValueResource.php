<?php

namespace App\Http\Resources\ProductOptionValue;

use App\Http\Resources\ProductOption\ProductOptionResource;
use App\Http\Resources\ProductOptionValueImage\ProductOptionValueImageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionValueResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'hex_code' => $this->hex_code,
            'order' => $this->order,
            'option' => new ProductOptionResource($this->whenLoaded('productOption')),
            'images' => ProductOptionValueImageResource::collection($this->whenLoaded('images')),
            'main_image' => $this->whenLoaded('images', function() {
                return new ProductOptionValueImageResource($this->images->firstWhere('is_main', true) ?? $this->images->first());
            }),
        ];
    }
}

