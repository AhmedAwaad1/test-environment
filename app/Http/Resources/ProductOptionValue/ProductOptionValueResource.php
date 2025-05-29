<?php

namespace App\Http\Resources\ProductOptionValue;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionValueResource extends JsonResource
{
    public function toArray(\Illuminate\Http\Request $request): array
    {
        return [
            'id' => $this->id,
            'product_option_id' => $this->product_option_id,
            'value' => $this->value,
            'hex_code' => $this->hex_code,
            'order' => $this->order,
            'images' => $this->whenLoaded('images', function() {
                return $this->images->map(function($image) {
                    return [
                        'id' => $image->id,
                        'image' => $image->image,
                    ];
                });
            }, []),
            'product_option' => $this->whenLoaded('productOption', function() {
                return [
                    'id' => $this->productOption->id,
                    'option_type' => $this->productOption->optionType ? [
                        'id' => $this->productOption->optionType->id,
                        'name' => $this->productOption->optionType->name,
                    ] : null,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

