<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

trait HasDateFilter
{
    /**
     * Scope a query to filter by date range.
     *
     * @param Builder $query
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string $column
     * @return Builder
     */
    public function scopeFilterByDate(Builder $query, $startDate = null, $endDate = null, $column = 'created_at')
    {
        if ($startDate) {
            $query->whereDate($column, '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->whereDate($column, '<=', Carbon::parse($endDate));
        }

        return $query;
    }
}
