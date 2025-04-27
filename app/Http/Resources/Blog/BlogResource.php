<?php

namespace App\Http\Resources\Blog;

use App\Http\Resources\BlogDetail\BlogDetailResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
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
            'title_en' => $this->title_en,
            'title_ar' => $this->title_ar,
            'content_en' => $this->content_en,
            'content_ar' => $this->content_ar,
            "cover_image" => $this->cover_image,
            'image' => $this->image,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
