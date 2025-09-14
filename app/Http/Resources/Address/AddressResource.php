<?php

namespace App\Http\Resources\Address;

use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\City\CityResource;
use App\Http\Resources\Country\CountryResource;
use App\Http\Resources\District\DistrictResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'address'        => $this->address,
            'phone'          => $this->phone,
            'is_default'     => $this->is_default,
            'shipping_price' => $this->getShippingPrice(),
            'user'           => new AuthResource($this->whenLoaded('user')),
            'country'        => $this->relationLoaded('country')
                ? new CountryResource($this->country)
                : ($this->city && $this->city->relationLoaded('country')
                    ? new CountryResource($this->city->country)
                    : null),
            'city'           => new CityResource($this->whenLoaded('city')),
            'district'       => new DistrictResource($this->whenLoaded('district')),
        ];
    }


    protected function getShippingPrice()
    {
        if ($this->district && $this->district->shipping_price > 0) {
            return (float)$this->district->shipping_price;
        }

        if ($this->city && $this->city->shipping_price > 0) {
            return (float)$this->city->shipping_price;
        }

        if ($this->city && $this->city->country && $this->city->country->shipping_price > 0) {
            return (float)$this->city->country->shipping_price;
        }

        return 0;
    }


}
