<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class FilterPipeline
{
    protected array $filters = [];

    /**
     * Set the filters to be applied.
     *
     * @param array $filters [key => FilterClass]
     * @return $this
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Apply the filters to the query.
     *
     * @param Builder $query
     * @param array $input
     * @return Builder
     */
    public function apply(Builder $query, array $input): Builder
    {
        foreach ($this->filters as $key => $filterClass) {
            $value = $this->resolveValue($key, $input);
            
            if ($value !== null || $this->shouldApplyEmpty($key)) {
                $filter = app($filterClass);
                $query = $filter->apply($query, $value);
            }
        }

        return $query;
    }

    /**
     * Resolve the value for a filter key from input.
     * Supports multi-key values (like price range).
     *
     * @param string|array $key
     * @param array $input
     * @return mixed
     */
    protected function resolveValue($key, array $input)
    {
        if (is_array($key)) {
            $values = [];
            foreach ($key as $k) {
                if (isset($input[$k])) {
                    $values[$k] = $input[$k];
                }
            }
            return empty($values) ? null : $values;
        }

        return $input[$key] ?? null;
    }

    /**
     * Check if the filter should be applied even if value is empty.
     * Useful for sort which might have a default.
     *
     * @param string|array $key
     * @return bool
     */
    protected function shouldApplyEmpty($key): bool
    {
        return $key === 'sort_by';
    }
}
