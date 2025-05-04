<?php

namespace App\Http\Resources\ProductVariant;

use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Color\ColorResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\ProductVariantImage\ProductVariantImageResource;
use App\Http\Resources\ProductVariantType\ProductVariantTypeResource;
use App\Http\Resources\Size\SizeResource;
use App\Models\ProductVariant;
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

        $data = [
            "id" => $this->id,
            'price' => $this->price,
            'price_after_discount' => $this->price_after_discount,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'is_active' => $this->is_active,
            'color_id' => $this->color_id,
            'size_id' => $this->size_id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'color' => new ColorResource($this->whenLoaded('color')),
            'size' => new SizeResource($this->whenLoaded('size')),
            'images' => ProductVariantImageResource::collection($this->whenLoaded('images')),
        ];

        if ($request->has('color_id') && $request->has('product_id')) {
            $variants = ProductVariant::where('product_id', $request->product_id)
                ->where('color_id', $request->color_id)
                ->with('size')
                ->get();

            $sizesWithQuantities = $variants->map(function ($variant) {
                return [
                    'size' => new SizeResource($variant->size),
                    'quantity' => $variant->quantity,
                ];
            })->unique('size.id')->values();

            $data['available_sizes_for_color'] = $sizesWithQuantities;
        }

        return $data;
    }
}
