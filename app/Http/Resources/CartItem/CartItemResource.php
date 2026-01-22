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
     * Format money values to 2 decimals as string.
     */
    private function money($v): string
    {
        return number_format((float)($v ?? 0), 2, '.', '');
    }

    public function toArray($request): array
    {
        $unitRaw    = (float)($this->unit_price ?? 0);
        $after      = $this->unit_price_after_discount;
        $unitAfter  = ($after !== null && (float)$after > 0) ? (float)$after : null;
        $perUnit    = $unitAfter ?? $unitRaw;
        $qty        = (int)($this->quantity ?? 0);

        return [
            'id'                         => $this->id,
            'product_id'                 => $this->product_id,
            'product_set_item_id'        => $this->product_set_item_id,
            'selected_product_ids'       => $this->selected_product_ids,
            'quantity'                   => $qty,

            'unit_price'                 => $this->money($unitRaw),
            'unit_price_after_discount'  => $unitAfter !== null ? $this->money($unitAfter) : null,
            'unit_price_applied'         => $this->money($perUnit),
            'total_price'                => $this->money($perUnit * $qty),

            'product_price_id'           => $this->product_price_id,
            'currency' => [
                'id'   => $this->currency?->id,
                'name' => $this->currency?->name,
            ],

            'product' => $this->whenLoaded('product', function () {
                $mainImage = optional(
                    $this->product->images->firstWhere('is_main', true)
                    ?? $this->product->images->first()
                )->image;

                return [
                    'id'         => $this->product->id,
                    'name_en'    => $this->product->name_en,
                    'name_ar'    => $this->product->name_ar,
                    'sku'        => $this->product->sku,
                    'main_image' => $mainImage,
                ];
            }),

            'variant' => $this->whenLoaded('productVariant', function () {
                $variantData = [
                    'id'    => $this->productVariant->id,
                    'sku'   => $this->productVariant->sku,
                    'title' => $this->productVariant->getTitle(),
                ];

                // Add color info if available
                $colorValue = $this->productVariant->optionValues->first(function($value) {
                    return $value->productOption && strtolower($value->productOption->optionType->name ?? '') === 'color';
                });

                if ($colorValue) {
                    $variantData['color_info'] = [
                        'name' => $colorValue->value,
                        'hex_code' => $colorValue->hex_code,
                    ];
                }

                return $variantData;
            }),

            'set_item' => $this->whenLoaded('productSetItem', function () {
                return [
                    'id'          => $this->productSetItem->id,
                    'name_en'     => $this->productSetItem->name_en,
                    'name_ar'     => $this->productSetItem->name_ar,
                    'sku'         => $this->productSetItem->sku,
                    'image'       => $this->productSetItem->image,
                    'description_en' => $this->productSetItem->description_en,
                    'description_ar' => $this->productSetItem->description_ar,
                    'products'    => $this->productSetItem->products->map(function ($product) {
                        $mainImage = optional(
                            $product->images->firstWhere('is_main', true)
                            ?? $product->images->first()
                        )->image;

                        // Find price for the cart's currency
                        $priceObj = $product->productPrices->firstWhere('currency_id', $this->currency_id);
                        $rawPrice = $priceObj ? $priceObj->price : 0;
                        $discountPrice = $priceObj ? $priceObj->price_after_discount : null;

                        return [
                            'id'         => $product->id,
                            'name_en'    => $product->name_en,
                            'name_ar'    => $product->name_ar,
                            'sku'        => $product->sku,
                            'main_image' => $mainImage,
                            'price'      => $this->money($rawPrice),
                            'price_after_discount' => $discountPrice !== null ? $this->money($discountPrice) : null,
                            'price_applied' => $this->money($discountPrice ?? $rawPrice),
                        ];
                    }),
                ];
            }),

            'selected_products' => $this->when($this->product_set_item_id && !empty($this->selected_product_ids), function () {
                $productIds = is_array($this->selected_product_ids) ? $this->selected_product_ids : json_decode($this->selected_product_ids, true);
                if (empty($productIds)) return [];

                return \App\Models\Product::whereIn('id', $productIds)->with(['images', 'productPrices'])->get()->map(function ($product) {
                    $mainImage = optional(
                        $product->images->firstWhere('is_main', true)
                        ?? $product->images->first()
                    )->image;

                    // Find price for the cart's currency
                    $priceObj = $product->productPrices->firstWhere('currency_id', $this->currency_id);
                    $rawPrice = $priceObj ? $priceObj->price : 0;
                    $discountPrice = $priceObj ? $priceObj->price_after_discount : null;

                    return [
                        'id'         => $product->id,
                        'name_en'    => $product->name_en,
                        'name_ar'    => $product->name_ar,
                        'sku'        => $product->sku,
                        'main_image' => $mainImage,
                        'price'      => $this->money($rawPrice),
                        'price_after_discount' => $discountPrice !== null ? $this->money($discountPrice) : null,
                        'price_applied' => $this->money($discountPrice ?? $rawPrice),
                    ];
                });
            }),
        ];
    }

}

