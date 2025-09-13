<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\ProductImage\ProductImageResource;
use App\Http\Resources\ProductOption\ProductOptionResource;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Http\Resources\SubCategory\SubCategoryResource;
use App\Http\Resources\ProductPrice\ProductPriceResource;
use App\Http\Resources\Currency\CurrencyResource;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;


class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $currency = app(GeoCurrencyService::class)->getCurrencyForRequest();

        $priceData = $this->productPrices->map(function ($price) {
            $after = $price->price_after_discount;
            $effective = (!is_null($after) && (float)$after > 0) ? $after : $price->price;

            return [
                'id' => $price->id,
                'price' => $price->price,
                'price_after_discount' => $effective, // fallback
                'currency_id' => $price->currency_id,
                'currency' => $price->currency?->name,
                'is_default' => $price->currency?->is_default,
            ];
        });

        $priceEntry = $this->productPrices->firstWhere('currency_id', $currency?->id)
            ?? $this->productPrices->firstWhere('currency.is_default', true);

        $after = $priceEntry?->price_after_discount;
        $effective = (!is_null($after) && (float)$after > 0) ? $after : $priceEntry?->price;

        $defaultPrice = [
            'price' => $priceEntry?->price ?? 0,
            'price_after_discount' => $effective, // fallback
            'currency_id' => $priceEntry?->currency_id,
            'currency' => $priceEntry?->currency?->name,
        ];

        $data = [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'sku' => $this->sku,
            'has_variants' => $this->has_variants,
            'status' => $this->is_active ?? true,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category_id' => $this->category_id,
            'sub_category_id' => $this->sub_category_id,
            'avg_rating' => round($this->reviews()->avg('rating'), 1),

            'prices' => $priceData,
            'default_price' => $defaultPrice,

            'category' => new CategoryResource($this->whenLoaded('category')),
            'sub_category' => new SubCategoryResource($this->whenLoaded('subCategory')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'main_image' => $this->whenLoaded('images', function () {
                return new ProductImageResource(
                    $this->images->firstWhere('is_main', true) ?? $this->images->first()
                );
            }),
        ];

        if ($this->has_variants) {
            $variants = $this->whenLoaded('productVariants', function () {
                return $this->productVariants->where('is_active', true);
            });

            $data = array_merge($data, [
                'quantity' => $this->quantity ?? null,
                'options' => ProductOptionResource::collection($this->whenLoaded('productOptions')),
                'variants' => ProductVariantResource::collection($variants),
                'available_options' => $this->whenLoaded('productOptions', function () {
                    return $this->productOptions->map(function ($option) {
                        $values = $option->values->map(function ($value) {
                            return [
                                'id' => $value->id,
                                'value' => $value->value,
                                'hex_code' => $value->hex_code,
                            ];
                        });

                        return [
                            'id' => $option->id,
                            'label' => $option->label,
                            'type' => optional($option->optionType)->name,
                            'values' => $values,
                        ];
                    });
                }),
            ]);
        } else {
            $data = array_merge($data, [
                'quantity' => $this->quantity,
                'options' => null,
                'variants' => null,
                'variants_count' => 0,
                'price_range' => null,
                'total_quantity' => $this->quantity,
            ]);
        }

        return $data;
    }
}

