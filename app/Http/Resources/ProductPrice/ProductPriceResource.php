<?php

namespace App\Http\Resources\ProductPrice;

use App\Http\Resources\Currency\CurrencyResource;
use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'currency_id' => $this->currency_id,
            'price' => $this->price,
            'price_after_discount' => $this->price_after_discount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'product' => new ProductResource($this->whenLoaded('product')),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
        ];
    }
} 