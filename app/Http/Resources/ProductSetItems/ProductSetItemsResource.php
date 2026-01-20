<?php

namespace App\Http\Resources\ProductSetItems;

use App\Http\Resources\Product\ProductListResource;
use App\Http\Resources\Category\CategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSetItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Calculate total prices from all products
        $calculatedPrices = $this->whenLoaded('products', function () {
            $pricesByCurrency = [];

            foreach ($this->products as $product) {
                foreach ($product->productPrices as $productPrice) {
                    $currencyId = $productPrice->currency_id;
                    
                    if (!isset($pricesByCurrency[$currencyId])) {
                        $pricesByCurrency[$currencyId] = [
                            'currency_id' => $currencyId,
                            'currency' => $productPrice->currency ? [
                                'id' => $productPrice->currency->id,
                                'code' => $productPrice->currency->code,
                                'symbol' => $productPrice->currency->symbol ?? $productPrice->currency->code,
                            ] : null,
                            'price' => 0,
                            'price_after_discount' => 0,
                        ];
                    }

                    $pricesByCurrency[$currencyId]['price'] += $productPrice->price;
                    $pricesByCurrency[$currencyId]['price_after_discount'] += ($productPrice->price_after_discount ?? $productPrice->price);
                }
            }

            return array_values($pricesByCurrency);
        }, []);

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'is_active' => $this->is_active,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'image' => $this->image,
            'products' => ProductListResource::collection($this->whenLoaded('products')),
            'products_count' => $this->whenLoaded('products', fn() => $this->products->count()),
            'total_prices' => $calculatedPrices,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

}
