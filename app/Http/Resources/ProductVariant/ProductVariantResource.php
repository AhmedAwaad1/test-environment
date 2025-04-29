<?php

namespace App\Http\Resources\ProductVariant;

use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\ProductVariantImage\ProductVariantImageResource;
use App\Http\Resources\ProductVariantType\ProductVariantTypeResource;
use App\Http\Resources\Size\SizeResource;
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
            'price' => $this->price,
            'price_after_discount' => $this->price_after_discount,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'is_active' => $this->is_active,
            'product' => new ProductResource($this->whenLoaded('product')),
            'color' => new CategoryResource($this->whenLoaded('color')),
            'size' => new SizeResource($this->whenLoaded('size')),
        ];
    }
}
