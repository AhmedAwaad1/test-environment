<?php

namespace App\Http\Resources\Address;

use App\Http\Resources\Auth\AuthResource;
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
            "id" => $this->id,
            'address' => $this->address,
            'phone' => $this->phone,
            'is_default' => $this->is_default,
            'user' => new AuthResource($this->whenLoaded('user')),
            'city' => new CountryResource($this->whenLoaded('city')),
            'district' => new DistrictResource($this->whenLoaded('district')),
        ];
    }
}
