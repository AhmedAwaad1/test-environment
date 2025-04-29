<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\ProductImage\ProductImageResource;
use App\Http\Resources\ProductType\ProductTypeResource;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'price' => $this->price,
            'price_after_discount' => $this->price_after_discount,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'product_type' => new ProductTypeResource($this->whenLoaded('productType')),
            'product_images' => ProductImageResource::collection($this->whenLoaded('images')),
            'product_variants' => ProductVariantResource::collection($this->whenLoaded('productVariants')),
        ];
    }
}
