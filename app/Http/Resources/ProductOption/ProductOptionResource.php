<?php

namespace App\Http\Resources\ProductOption;

use App\Http\Resources\ProductOptionType\ProductOptionTypeResource;
use App\Http\Resources\ProductOptionValue\ProductOptionValueResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order' => $this->order,
            'option_type' => new ProductOptionTypeResource($this->whenLoaded('optionType')),
            'values' => ProductOptionValueResource::collection($this->whenLoaded('values')),
        ];
    }
}

