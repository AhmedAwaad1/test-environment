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

    public function toArray($request): array
    {
        $perUnit = $this->unit_price_after_discount ?? $this->unit_price;

        $mainImage = optional($this->product->images->firstWhere('is_main', true) ?? $this->product->images->first())->image;

        return [
            'id'                       => $this->id,
            'product_id'               => $this->product_id,
            'quantity'                 => (int) $this->quantity,

            'unit_price'               => (float) $this->unit_price,
            'unit_price_after_discount'=> $this->unit_price_after_discount !== null ? (float) $this->unit_price_after_discount : null,
            'unit_price_applied'       => (float) $perUnit,
            'total_price'              => (float) $this->total_price,

            'product_price_id'         => $this->product_price_id,
            'currency' => [
                'id'   => $this->currency?->id,
                'name' => $this->currency?->name,
            ],

            'product' => $this->whenLoaded('product', function () use ($mainImage) {
                return [
                    'id'          => $this->product->id,
                    'name_en'     => $this->product->name_en,
                    'name_ar'     => $this->product->name_ar,
                    'sku'         => $this->product->sku,
                    'main_image'  => $mainImage,
                ];
            }),
        ];
    }
}

