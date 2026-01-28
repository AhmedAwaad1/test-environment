<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * Generate a unique cache key based on prefix and parameters.
     */
    public static function generateKey(string $prefix, array $params = []): string
    {
        // Add locale and currency to params as they are global factors
        $params['locale'] = app()->getLocale();
        $params['currency'] = session('currency_id') ?? 'default';

        // Sort params to ensure consistency
        ksort($params);
        
        $queryString = http_build_query($params);
        $key = "{$prefix}_" . md5($queryString);

        // Track this key in the manifest for prefix-based invalidation
        static::addToManifest($prefix, $key);

        return $key;
    }

    /**
     * Add a cache key to the prefix manifest.
     */
    protected static function addToManifest(string $prefix, string $key): void
    {
        $manifestKey = "manifest_{$prefix}";
        $keys = Cache::get($manifestKey, []);
        
        if (!in_array($key, $keys)) {
            $keys[] = $key;
            Cache::forever($manifestKey, $keys);
        }
    }

    /**
     * Clear all cache keys associated with a specific prefix.
     */
    public static function clearByPrefix(string $prefix): void
    {
        $manifestKey = "manifest_{$prefix}";
        $keys = Cache::get($manifestKey, []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget($manifestKey);
    }
}
