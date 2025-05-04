<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\CartItem\CartItemResource;
use App\Http\Resources\Country\CountryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'user_id' => $this->user_id,
            'session_id' => $this->session_id,
            'cupon_code' => $this->coupon_code,
            'discount_amount' => (int) $this->discount_amount,
            'total_price' => (int) $this->total_price,
            'cart_items' => CartItemResource::collection($this->whenLoaded('cartItems')),
            'user' => new AuthResource($this->whenLoaded('user')),
        ];
    }
}
