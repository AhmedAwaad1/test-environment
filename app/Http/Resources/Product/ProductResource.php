<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\ProductImage\ProductImageResource;
use App\Http\Resources\SubCategory\SubCategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $currencyId = $request->get('currency_id');

        $prices = $this->productPrices ?? collect();

        // ✅ All prices
        $priceData = $prices->map(function ($price) {
            $effective = ($price->price_after_discount && $price->price_after_discount > 0)
                ? $price->price_after_discount
                : $price->price;

            return [
                'id' => $price->id,
                'price' => $price->price,
                'price_after_discount' => $effective,
                'currency_id' => $price->currency_id,
                'currency' => $price->currency?->name,
                'is_default' => (bool) $price->currency?->is_default,
            ];
        });

        // ✅ Select correct default price
        $priceEntry =
            $prices->firstWhere('currency_id', $currencyId)
            ?? $prices->first(fn ($p) => $p->currency?->is_default);

        $defaultPrice = [
            'price' => $priceEntry?->price ?? 0,
            'price_after_discount' =>
                ($priceEntry?->price_after_discount > 0)
                    ? $priceEntry->price_after_discount
                    : $priceEntry?->price,
            'currency_id' => $priceEntry?->currency_id,
            'currency' => $priceEntry?->currency?->name,
        ];

        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'sku' => $this->sku,

            'has_variants' => (bool) $this->has_variants,
            'status' => (bool) $this->is_active,

            'category_id' => $this->category_id,
            'sub_category_id' => $this->sub_category_id,

            // 🔥 Comes from withAvg (NO query here)
            'avg_rating' => round($this->reviews_avg_rating ?? 0, 1),

            'prices' => $priceData,
            'default_price' => $defaultPrice,

            'is_best_seller' => (bool) $this->is_best_seller,
            'is_new_arrival' => (bool) $this->is_new_arrival,

            'category' => new CategoryResource($this->whenLoaded('category')),
            'sub_category' => new SubCategoryResource($this->whenLoaded('subCategory')),

            'images' => ProductImageResource::collection($this->whenLoaded('images')),

            'main_image' => $this->whenLoaded('images', function () {
                $img = $this->images->firstWhere('is_main', true)
                    ?? $this->images->first();

                return $img ? new ProductImageResource($img) : null;
            }),

            'quantity' => $this->quantity,
        ];
    }
}
