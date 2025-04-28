<?php

namespace App\Http\Resources\Banner;

use App\Http\Resources\Country\CountryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
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
            'image' => $this->image,
            'url' => $this->url,
            'product_id' => $this->product_id,
            'category_id' => $this->category_id,
            'product_type_id' => $this->product_type_id,
        ];
    }
}
