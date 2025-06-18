<?php

namespace App\Http\Resources\City;

use App\Http\Resources\Country\CountryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
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
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'country_id' => $this->country_id,
            'country' => new CountryResource($this->whenLoaded('country')),
            'shipping_price' => $this->shipping_price,
        ];
    }
}
