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
        // Get the variant's color option value if it exists
        $colorValue = $this->whenLoaded('optionValues', function() {
            return $this->optionValues->first(function($value) {
                return strtolower($value->productOption->name) === 'color';
            });
        });

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'price' => $this->price,
            'price_after_discount' => $this->price_after_discount,
            'quantity' => $this->quantity,
            'barcode' => $this->barcode,
            'weight' => $this->weight,
            'is_active' => $this->is_active,
            'order' => $this->order,
            'title' => $this->getVariantTitle(),
            'attributes' => $this->getVariantAttributes(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Images - only included for color variants
            'images' => $colorValue ? ProductImageResource::collection($this->whenLoaded('images')) : null,
            'main_image' => $colorValue ? $this->whenLoaded('images', function() {
                return new ProductImageResource($this->images->firstWhere('is_main', true) ?? $this->images->first());
            }) : null,

            // Color information if this is a color variant
            'color_info' => $colorValue ? [
                'name' => $colorValue->value,
                'hex_code' => $colorValue->standard_value,
            ] : null,

            // Relationships
            'product' => new ProductResource($this->whenLoaded('product')),
            'option_values' => ProductOptionValueResource::collection($this->whenLoaded('optionValues')),
        ];
    }

    protected function getVariantTitle()
    {
        if (!$this->optionValues->isEmpty()) {
            return $this->optionValues->map(function($value) {
                return $value->value;
            })->join(' / ');
        }
        return null;
    }

    protected function getVariantAttributes()
    {
        if (!$this->optionValues->isEmpty()) {
            return $this->optionValues->mapWithKeys(function($value) {
                return [$value->productOption->name => $value->value];
            });
        }
        return null;
    }
}
