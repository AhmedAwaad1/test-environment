<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\ProductImage\ProductImageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    public function toArray($request)
    {
        $currencyId = $request->get('currency_id');

        $prices = $this->productPrices ?? collect();

        // Select correct default price
        $priceEntry =
            $prices->firstWhere('currency_id', $currencyId)
            ?? $prices->first(fn ($p) => $p->currency?->is_default)
            ?? $prices->first();

        // Check if we should use variant price
        if ($this->has_variants && $this->relationLoaded('productVariants') && $this->productVariants->isNotEmpty()) {
            $firstVariant = $this->productVariants->first();
            $defaultPrice = [
                'price' => $firstVariant->price ?? 0,
                'price_after_discount' => $firstVariant->price_after_discount ?? $firstVariant->price ?? 0,
            ];
        } else {
            $defaultPrice = [
                'price' => $priceEntry?->price ?? 0,
                'price_after_discount' =>
                    ($priceEntry?->price_after_discount > 0)
                        ? $priceEntry->price_after_discount
                        : $priceEntry?->price ?? 0,
            ];
        }

        // Compute main_image server-side
        $mainImage = null;
        if ($this->has_variants && $this->relationLoaded('productVariants') && $this->productVariants->isNotEmpty()) {
            $firstVariant = $this->productVariants->first();
            if ($firstVariant->relationLoaded('images') && $firstVariant->images->isNotEmpty()) {
                $mainImage = $firstVariant->images->firstWhere('is_main', true) ?? $firstVariant->images->first();
            }
        }
        
        if (!$mainImage && $this->relationLoaded('images') && $this->images->isNotEmpty()) {
            $mainImage = $this->images->firstWhere('is_main', true) ?? $this->images->first();
        }

        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'has_variants' => (bool) $this->has_variants,
            'created_at' => $this->created_at,
            'avg_rating' => round($this->reviews_avg_rating ?? 0, 1),
            'default_price' => $defaultPrice,
            'category' => $this->whenLoaded('category', fn () => [
                'name_en' => $this->category->name_en,
            ]),
            'sub_category' => $this->whenLoaded('subCategory', fn () => [
                'name_en' => $this->subCategory->name_en,
            ]),
            'main_image' => $mainImage ? new ProductImageResource($mainImage) : null,
            'main_image_url' => $mainImage ? $mainImage->image : null,
        ];
    }
}


