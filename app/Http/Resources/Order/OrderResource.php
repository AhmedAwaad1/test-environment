<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\City\CityResource;
use App\Http\Resources\Country\CountryResource;
use App\Http\Resources\OrderItem\OrderItemResource;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'order_number' => $this->order_number,

            'subtotal'        => (string)number_format((float)$this->subtotal, 2, '.', ''),
            'discount_amount' => (string)number_format((float)($this->discount_amount ?? 0), 2, '.', ''),
            'shipping_price'  => (string)number_format((float)$this->shipping_price, 2, '.', ''),
            'grand_total'     => (string)number_format((float)$this->total_price, 2, '.', ''),

            'coupon_code' => $this->coupon_code,

            'currency' => [
                'id'     => $this->currency_id,
                'name'   => $this->whenLoaded('currency', fn() => $this->currency?->name),
            ],

            'payment_method'  => $this->payment_method,
            'status'          => $this->status,
            'tracking_number' => $this->tracking_number,
            'notes'           => $this->notes,

            'user'    => new AuthResource($this->whenLoaded('user')),
            'address' => new AddressResource($this->whenLoaded('address')),

            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),

            'created_at' => $this->created_at,
        ];
    }
}
