<?php

namespace App\Http\Services\Product;

class ProductFilterService
{
    /**
     * Extract and normalize only relevant filter parameters.
     */
    public function getRelevantFilters($request): array
    {
        $relevantKeys = [
            'search',
            'category_id',
            'sub_category_id',
            'is_best_seller',
            'best_seller',
            'is_new_arrival',
            'new_arrival',
            'is_active',
            'has_variants',
            'in_stock',
            'filters',
            'min_price',
            'max_price',
            'sort_by',
            'sort_direction',
            'page',
            'per_page',
            'resource',
            'fields',
        ];

        $filters = [];
        foreach ($relevantKeys as $key) {
            if ($request->has($key)) {
                $value = $request->get($key);
                
                // Normalize empty values
                if ($value === '' || $value === null) {
                    continue;
                }

                // Normalize boolean strings
                if ($value === 'true' || $value === '1') $value = 1;
                if ($value === 'false' || $value === '0') $value = 0;

                // Normalize sort direction
                if ($key === 'sort_direction') {
                    $value = strtolower($value) === 'desc' ? 'desc' : 'asc';
                }

                // Normalize per_page
                if ($key === 'per_page') {
                    $value = max(1, min(100, (int) $value));
                }

                // Normalize numeric strings
                if (is_numeric($value) && !in_array($key, ['search', 'sort_by', 'sort_direction', 'resource', 'fields', 'per_page'])) {
                    $value = (float) $value;
                }

                // Normalize arrays (e.g., attribute filters)
                if (is_array($value)) {
                    $this->normalizeArray($value);
                }

                $filters[$key] = $value;
            }
        }

        // Add calculated flags
        $filters['is_list_resource'] = ($request->get('resource') === 'list' || $request->get('fields') === 'list');
        $filters['should_paginate'] = $request->filled('per_page');

        return $filters;
    }

    /**
     * Recursively normalize arrays for consistent cache keys.
     */
    public function normalizeArray(array &$array): void
    {
        ksort($array);
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->normalizeArray($value);
            } elseif (is_string($value)) {
                $value = strtolower(trim($value));
            }
        }
    }

    /**
     * Get default filter values.
     */
    public function getDefaults(): array
    {
        return [
            'per_page' => 15,
            'sort_by' => 'created_at',
            'sort_direction' => 'desc',
            'is_active' => 1,
        ];
    }

    /**
     * Check if a specific filter is active.
     */
    public function hasFilter(array $filters, string $key): bool
    {
        return isset($filters[$key]) && $filters[$key] !== null && $filters[$key] !== '';
    }

    /**
     * Merge request filters with defaults.
     */
    public function mergeWithDefaults(array $filters): array
    {
        return array_merge($this->getDefaults(), $filters);
    }
}
