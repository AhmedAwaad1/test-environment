<?php

namespace App\Http\Services\Product;

use App\Helpers\CacheHelper;
use Illuminate\Support\Facades\Cache;

class ProductCacheService
{
    public function __construct(
        protected ProductFilterService $productFilterService
    ) {}

    /**
     * Generate a normalized cache key for product requests.
     */
    public function generateProductCacheKey($request): string
    {
        $filters = $this->productFilterService->getRelevantFilters($request);
        return $this->getCacheKey($filters);
    }

    /**
     * Get cache key from filters.
     */
    public function getCacheKey(array $filters): string
    {
        return CacheHelper::generateKey('products', $filters);
    }

    /**
     * Wrap a product-related operation with caching.
     */
    public function remember(array $filters, \Closure $callback)
    {
        $cacheKey = $this->getCacheKey($filters);
        return Cache::remember($cacheKey, now()->addHours(24), $callback);
    }

    /**
     * Invalidate product cache.
     */
    public function invalidate(): void
    {
        CacheHelper::forgetByPattern('products');
    }

    /**
     * Invalidate cache for a specific product.
     */
    public function invalidateProduct(int $id): void
    {
        $this->forget(['id' => $id]);
    }

    /**
     * Manually forget a specific cache entry.
     */
    public function forget(array $filters): void
    {
        $cacheKey = $this->getCacheKey($filters);
        Cache::forget($cacheKey);
    }

    /**
     * Generate tags for product cache if supported.
     */
    public function getTags(array $filters): array
    {
        $tags = ['products'];
        if (isset($filters['category_id'])) {
            $tags[] = 'category_' . $filters['category_id'];
        }
        return $tags;
    }
}
