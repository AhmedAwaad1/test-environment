<?php

namespace App\Http\Resources\VaraintSize;

use App\Http\Resources\Country\CountryResource;
use App\Http\Resources\Size\SizeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VaraintSizeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'size_id' => $this->size_id,
            'quantity' => $this->quantity,
            'size' => new SizeResource($this->size),
        ];
    }
}
