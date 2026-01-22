<?php

namespace App\Http\Resources\Favorite;

use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\Country\CountryResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\ProductSetItems\ProductSetItemsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_set_item_id' => $this->product_set_item_id,
            'user_id' => $this->user_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'product_set_item' => new ProductSetItemsResource($this->whenLoaded('productSetItem')),
            'user' => new AuthResource($this->whenLoaded('user')),
        ];
    }
}
