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

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class ProjectsStatsWidget extends Widget
{
    protected string $view = 'filament.widgets.projects-stats-widget';

    protected ?string $heading = 'Projects';

    protected static ?int $sort = 2;

    public ?string $filter = 'ALL';

    protected function getProjectsCount(): int
    {
        $filter = $this->filter ?? 'ALL';

        $query = Project::query();

        switch ($filter) {
            case 'TODAY':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'MTD':
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'QTD':
                $quarter = Carbon::now()->quarter;
                $year = Carbon::now()->year;
                $query->whereBetween('created_at', [
                    Carbon::createFromDate($year, ($quarter - 1) * 3 + 1, 1)->startOfDay(),
                    Carbon::now()->endOfDay(),
                ]);
                break;
            case 'YTD':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
            case 'ALL':
            default:
                // No filter for all time
                break;
        }

        return $query->count();
    }

    protected function getFilters(): ?array
    {
        return [
            'ALL' => 'All Time',
            'TODAY' => 'Today',
            'MTD' => 'Month To Date',
            'QTD' => 'Quarter To Date',
            'YTD' => 'Year To Date',
        ];
    }

    public function getViewData(): array
    {
        return [
            'count' => $this->getProjectsCount(),
            'filters' => $this->getFilters(),
            'activeFilter' => $this->filter,
        ];
    }
}
