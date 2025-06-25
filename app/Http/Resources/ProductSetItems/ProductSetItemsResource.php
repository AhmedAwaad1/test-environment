<?php

namespace App\Http\Resources\ProductSetItems;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductSetItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name_en' => $this->product->name_en,
                    'name_ar' => $this->product->name_ar,
                ];
            }),
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'how_to_use_en' => $this->how_to_use_en,
            'how_to_use_ar' => $this->how_to_use_ar,
            'features_en' => json_decode($this->features_en),
            'features_ar' => json_decode($this->features_ar),
            'image' => $this->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

} 