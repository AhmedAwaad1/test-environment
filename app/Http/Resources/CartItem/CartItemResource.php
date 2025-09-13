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
        ];
    }

}

