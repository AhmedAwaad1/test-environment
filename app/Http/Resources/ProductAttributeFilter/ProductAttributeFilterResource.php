<?php

namespace App\Http\Resources\ProductAttributeFilter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttributeFilterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $allValues = $this->productOptions
            ->flatMap(fn($option) => $option->values)
            ->pluck('value')
            ->map(fn($val) => ucfirst(strtolower($val)))
            ->unique()
            ->values();

        return [
            'attribute_name' => $this->name,
            'values' => $allValues->map(fn($val) => [
                'display_name' => $val,
            ])->values(),
        ];
    }
}
