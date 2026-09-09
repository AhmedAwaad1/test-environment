<?php

namespace App\Http\Resources\ERP;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ErpProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentPrice = $this->productPrices?->first()?->price ?? 0;
        $mainImage = $this->images?->firstWhere('is_main', true) ?? $this->images?->first();

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name_ar,
            'name2' => $this->name_en,
            'description' => $this->description_ar,
            'description2' => $this->description_en,
            'price' => (float) $currentPrice,
            'stock' => (int) $this->quantity,
            'image_url' => $mainImage?->getRawOriginal('image'),
            'group_code' => $this->erp_group_code,
            'group2_code' => $this->erp_group2_code,
            'group3_code' => $this->erp_group3_code,
            'category_id' => $this->category_id,
            'sub_category_id' => $this->sub_category_id,
            'sub_sub_category_id' => $this->sub_sub_category_id,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'code' => $this->category->code,
                'name' => $this->category->name_ar,
            ] : null,
            'sub_category' => $this->subCategory ? [
                'id' => $this->subCategory->id,
                'code' => $this->subCategory->code,
                'name' => $this->subCategory->name_ar,
            ] : null,
            'sub_sub_category' => $this->subSubCategory ? [
                'id' => $this->subSubCategory->id,
                'code' => $this->subSubCategory->code,
                'name' => $this->subSubCategory->name_ar,
            ] : null,
        ];
    }
}
