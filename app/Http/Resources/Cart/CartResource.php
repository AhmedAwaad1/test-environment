<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\CartItem\CartItemResource;
use App\Http\Resources\Country\CountryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Country;


class CartResource extends JsonResource
{
    /**
     * Format any money-like value to 2 decimals as string.
     */
    private function money($v): string
    {
        return number_format((float)($v ?? 0), 2, '.', '');
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $totalPrice     = (float)($this->total_price ?? 0);
        $totalAfterDisc = (float)($this->total_price_after_discount ?? $totalPrice);
        $discountAmount = (float)($this->discount_amount ?? 0);
        $grandTotal     = max(0, $totalAfterDisc - $discountAmount);

        return [
            'id'                         => $this->id,
            'user_id'                    => $this->user_id,
            'session_id'                 => $this->session_id,
            'discount_amount'            => $this->money($discountAmount),
            'total_price'                => $this->money($totalPrice),
            'total_price_after_discount' => $this->money($totalAfterDisc),
            'grand_total'                => $this->money($grandTotal), // Shipping decoupled

            'cart_items' => CartItemResource::collection($this->whenLoaded('cartItems')),
            'user'       => new AuthResource($this->whenLoaded('user')),
        ];
    }
}