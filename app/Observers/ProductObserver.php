<?php

namespace App\Observers;

use App\Models\Product;
use App\Helpers\CacheHelper;

class ProductObserver
{
    /**
     * Handle the Product "saved" event.
     */
    public function saved(Product $product): void
    {
        CacheHelper::clearByPrefix('products');
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        CacheHelper::clearByPrefix('products');
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        CacheHelper::clearByPrefix('products');
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        CacheHelper::clearByPrefix('products');
    }
}
