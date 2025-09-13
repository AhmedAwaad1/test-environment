<?php

namespace App\Support;

use App\Models\Currency;
use App\Models\Product;

class Pricing
{
    public static function pickPriceEntry(Product $product, ?Currency $currency)
    {
        $entry = $product->productPrices->firstWhere('currency_id', $currency?->id);

        if (!$entry) {
            $entry = $product->productPrices->firstWhere('currency.is_default', true);
        }

        return $entry;
    }

    public static function unitToUse($priceEntry): float
    {
        return (float)($priceEntry->price_after_discount ?? $priceEntry->price);
    }
}