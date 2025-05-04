<?php

namespace App\Http\Resources\CartItem;

use App\Http\Resources\Country\CountryResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
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
            'product_variant_id' => $this->product_variant_id,
            'quantity' => $this->quantity,
            'price' => (int) $this->price,
            'total_price' => (int) $this->total_price,
            'product_variant' => new ProductVariantResource($this->whenLoaded('productVariant')),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
