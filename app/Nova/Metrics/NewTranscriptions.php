<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Nova\Metrics;

use App\Models\PanoptesTranscription;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Nova;

class NewTranscriptions extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        // Use custom counting logic instead of Nova's count() method for MongoDB compatibility
        $query = PanoptesTranscription::query();

        // Apply date range filtering based on the selected range
        if ($request->range && $request->range !== 'ALL') {
            $query = $this->applyDateRangeFilter($query, $request->range);
        }

        $count = $query->count();

        // Return a proper ValueResult
        return $this->result($count);
    }

    /**
     * Apply date range filter to the query
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $range
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyDateRangeFilter($query, $range)
    {
        switch ($range) {
            case 'TODAY':
                return $query->whereDate('created_at', today());
            case 'MTD':
                return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            case 'QTD':
                $quarter = ceil(now()->month / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $quarter * 3;

                return $query->whereMonth('created_at', '>=', $startMonth)
                    ->whereMonth('created_at', '<=', $endMonth)
                    ->whereYear('created_at', now()->year);
            case 'YTD':
                return $query->whereYear('created_at', now()->year);
            default:
                return $query;
        }
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            'ALL' => Nova::__('All Time'),
            'TODAY' => Nova::__('Today'),
            'MTD' => Nova::__('Month To Date'),
            'QTD' => Nova::__('Quarter To Date'),
            'YTD' => Nova::__('Year To Date'),
        ];
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'new-transcriptions';
    }
}
