<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Color\ColorResource;
use App\Http\Resources\ProductImage\ProductImageResource;
use App\Http\Resources\ProductType\ProductTypeResource;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Http\Resources\Size\SizeResource;
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
            'price' => (int) $this->price,
            'price_after_discount' => (int) $this->price_after_discount,
            'sizes' => SizeResource::collection(
                collect($this->productVariants)
                    ->pluck('variantSizes') // لو sizes جاية من علاقة variantSizes
                    ->flatten()
                    ->pluck('size')
                    ->unique('id')
                    ->values()
            ),

            'colors' => ColorResource::collection(
                collect($this->productVariants)
                    ->pluck('color')
                    ->unique('id')
                    ->values()
            ),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'product_type' => new ProductTypeResource($this->whenLoaded('productType')),
            'product_images' => ProductImageResource::collection($this->whenLoaded('images')),
            'product_variants' => ProductVariantResource::collection($this->whenLoaded('productVariants')),
        ];
    }
}
