<?php

namespace App\Http\Resources\ProductVariant;

use App\Http\Resources\Color\ColorResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\ProductVariantImage\ProductVariantImageResource;
use App\Http\Resources\VaraintSize\VaraintSizeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
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
            'price' => (int) $this->price,
            'price_after_discount' => (int) $this->price_after_discount,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'is_active' => $this->is_active,
            'color_id' => $this->color_id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'color' => new ColorResource($this->whenLoaded('color')),
            'variant_sizes' => VaraintSizeResource::collection($this->whenLoaded('variantSizes')),
            'images' => ProductVariantImageResource::collection($this->whenLoaded('images')),
        ];

    }
}
