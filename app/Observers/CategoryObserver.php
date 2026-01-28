<?php

namespace App\Observers;

use App\Models\Category;
use App\Helpers\CacheHelper;

class CategoryObserver
{
    /**
     * Handle the Category "saved" event.
     */
    public function saved(Category $category): void
    {
        CacheHelper::clearByPrefix('categories');
        // Since products might be filtered by category, clearing products cache too is safer
        CacheHelper::clearByPrefix('products');
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        CacheHelper::clearByPrefix('categories');
        CacheHelper::clearByPrefix('products');
    }
}
