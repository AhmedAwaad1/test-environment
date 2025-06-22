<?php

namespace App\Http\Resources\Review;

use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'comment' => $this->comment,
            'rating' => $this->rating,
            'user' => new AuthResource($this->whenLoaded('user')),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
