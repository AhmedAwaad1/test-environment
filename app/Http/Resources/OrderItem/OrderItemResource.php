<?php

namespace App\Http\Resources\OrderItem;

use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\City\CityResource;
use App\Http\Resources\Country\CountryResource;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'product_id'   => $this->product_id,
            'product_name' => $this->product_name,

            'product_price_id' => $this->product_price_id,

            'unit_price'                => (string)number_format((float)$this->unit_price, 2, '.', ''),
            'unit_price_after_discount' => $this->unit_price_after_discount !== null
                ? (string)number_format((float)$this->unit_price_after_discount, 2, '.', '')
                : null,

            'quantity'    => (int)$this->quantity,
            'total_price' => (string)number_format((float)$this->total_price, 2, '.', ''),
        ];
    }
}
