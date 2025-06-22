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
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'slug' => $this->slug,
            'content_en' => $this->content_en,
            'content_ar' => $this->content_ar,
            'image' => $this->image,
            'is_active' => $this->is_active,
            'published_at' => $this->published_at,
        ];
    }
}
