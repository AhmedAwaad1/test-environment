<?php

namespace App\Http\Resources\ProductVariant;

use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\ProductImage\ProductImageResource;
use App\Http\Resources\ProductOptionValue\ProductOptionValueResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'sku' => $this->sku,
            'price' => $this->price,
            'price_after_discount' => $this->price_after_discount,
            'quantity' => $this->quantity,
            'barcode' => $this->barcode,
            'weight' => $this->weight,
            'is_active' => $this->is_active,
            'order' => $this->order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'main_image' => $this->whenLoaded('images', function() {
                return new ProductImageResource($this->images->firstWhere('is_main', true) ?? $this->images->first());
            }),
            'product' => new ProductResource($this->whenLoaded('product')),
            'option_values' => ProductOptionValueResource::collection($this->whenLoaded('optionValues')),
        ];

        // Add variant title and attributes only if optionValues are loaded
        if ($this->relationLoaded('optionValues') && !$this->optionValues->isEmpty()) {
            $data['title'] = $this->optionValues->map(function($value) {
                return $value->value;
            })->join(' / ');

            $data['attributes'] = $this->optionValues->mapWithKeys(function($value) {
                return [$value->productOption->name => $value->value];
            });

            // Handle color information
            $colorValue = $this->optionValues->first(function($value) {
                return $value->productOption && strtolower($value->productOption->name) === 'color';
            });

            if ($colorValue) {
                $data['color_info'] = [
                    'name' => $colorValue->value,
                    'hex_code' => $colorValue->hex_code,
                ];
            } else {
                $data['color_info'] = null;
            }
        } else {
            $data['title'] = null;
            $data['attributes'] = null;
            $data['color_info'] = null;
        }

        return $data;
    }
}
