<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    /**
     * Apply the filter to the query.
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function apply(Builder $query, $value): Builder;
}
