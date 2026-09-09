<?php

namespace App\Http\Resources\ERP;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ErpCategoryResource extends JsonResource
{
    protected int $level;

    public function __construct($resource, int $level = 1)
    {
        parent::__construct($resource);
        $this->level = $level;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name_ar ?? $this->name_en,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'level' => $this->level,
            'ucode1' => $this->ucode1 ?? 0,
            'ucode2' => $this->ucode2 ?? 0,
            'slug' => $this->slug,
            'notes' => $this->notes,
        ];
    }
}
