<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\City\CityResource;
use App\Http\Resources\Country\CountryResource;
use App\Http\Resources\OrderItem\OrderItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'subtotal' => $this->subtotal,
            'shipping_price' => $this->shipping_price,
            'total_price' => $this->total_price,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'tracking_number' => $this->tracking_number,
            'notes' => $this->notes,
            'user' => new AuthResource($this->whenLoaded('user')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
        ];
    }
}
