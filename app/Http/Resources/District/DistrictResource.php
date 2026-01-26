<?php

namespace App\Http\Resources\District;

use App\Http\Resources\City\CityResource;
use App\Http\Resources\Country\CountryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
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
            'city_id' => $this->city_id,
            'city_name_en' => $this->city->name_en ?? null,
            'city_name_ar' => $this->city->name_ar ?? null,
            'shipping_price' =>$this->shipping_price,
        ];
    }
}
