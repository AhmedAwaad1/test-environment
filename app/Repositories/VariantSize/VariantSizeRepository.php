<?php

namespace App\Repositories\VariantSize;

use App\Models\VariantSize;

class VariantSizeRepository
{
    public function findVariantSizeByVariantId($productVariantId, $sizeId)
    {
        return VariantSize::where('product_variant_id', $productVariantId)
            ->where('size_id', $sizeId)
            ->first();
    }

}
