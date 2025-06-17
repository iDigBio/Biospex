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

namespace App\Services\Chart;

use App\Models\AmChart;
use App\Models\Expedition;
use App\Models\Project;
use App\Services\Transcriptions\PanoptesTranscriptionService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

/**
 * Class TranscriptionChartService
 */
class TranscriptionChartService
{
    private mixed $amChartData;

    private mixed $projectChartSeries;

    private mixed $projectChartSeriesFile;

    private mixed $begin;

    private mixed $end;

    private mixed $yearDaysArray;

    /**
     * TranscriptionChartService constructor.
     */
    public function __construct(
        protected PanoptesTranscriptionService $panoptesTranscriptionService,
        protected Carbon $carbon,
        protected Filesystem $filesystem
    ) {}

    /**
     * Process project for amchart.
     */
    public function process(Project $project): void
    {
        $project->load([
            'amChart',
            'expeditions' => function ($q) {
                $q->with('stat')->has('stat');
                $q->with('panoptesProject')->has('panoptesProject');
            },
        ]);

        $this->checkNewChart($project);

        $this->resetTemplates();

        $years = $this->setYearsArray($project->id);
        if ($years === null) {
            return;
        }

        $years->reject(function ($year) use ($project) {
            return isset($project->amChart['series'][$year]) && $year !== $this->carbon::now()->year;
        })->each(function ($year) use ($project) {
            $this->setBeginEndOfYear($year);
            $this->setAmChartData($year);
            $this->setYearDaysArray($year);
            $this->buildCompleteChartData($project, $year);
        });

        $this->amChartData->transform(function ($value, $key) {
            return array_values($value->toArray());
        });

        $project->amChart->data = $project->amChart->data === null ? [] : $project->amChart->data;
        $project->amChart->series = $project->amChart->series === null ? [] : $project->amChart->series;

        $project->amChart->data = array_replace($project->amChart->data, $this->amChartData->toArray());
        $project->amChart->series = array_replace($project->amChart->series, $this->projectChartSeries);

        $project->amChart->save();

    }

    /**
     * Check if this is a new chart. Create and load if new.
     */
    protected function checkNewChart(Project &$project): void
    {
        if ($project->amChart === null) {
            $amChart = new AmChart;
            $amChart->data = [];
            $amChart->series = [];
            $project->amChart()->save($amChart);
            $project->load('amChart');
        }
    }

    /**
     * Reset the templates for each project.
     */
    protected function resetTemplates(): void
    {
        $this->amChartData = collect();
        $this->projectChartSeries = [];
        $file = config('config.project_chart_series');
        $this->projectChartSeriesFile = json_decode($this->filesystem->get($file), true);
    }

    /**
     * Build complete series and data for chart for year.
     */
    protected function buildCompleteChartData($project, $year): void
    {
        $count = false;
        $project->expeditions->each(function ($expedition) use ($year, &$count) {
            $dateCount = $this->transcriptionCountPerDate($expedition->panoptesProject->panoptes_workflow_id);
            if ($dateCount->isEmpty()) {
                return;
            }

            $count = true;
            $completeDates = $this->processExpedition($expedition, $year, $dateCount);

            $this->amChartData[$year] = $this->amChartData[$year]->mergeRecursive($completeDates);
        });

        if (! $count) {
            unset($this->amChartData[$year]);
        }
    }

    /**
     * Set years array.
     * $this->carbon::parse('first day of January next year')->subSecond();
     */
    public function setYearsArray(int $projectId): ?Collection
    {
        $earliest_date = $this->panoptesTranscriptionService->getMinFinishedAtDateByProjectId($projectId);
        $latest_date = $this->panoptesTranscriptionService->getMaxFinishedAtDateByProjectId($projectId);

        if ($earliest_date === null || $latest_date === null) {
            return null;
        }

        $years = range($this->carbon::parse($earliest_date)->year, $this->carbon::parse($latest_date)->year);
        rsort($years);

        return collect($years);
    }

    /**
     * Return first day and last day of given year, or if current year, get today.
     */
    protected function setBeginEndOfYear(int $year): void
    {
        $this->begin = $this->carbon::parse('first day of January '.$year);
        $this->end = $year === $this->carbon::now()->year ? $this->carbon::parse('today')->addDay()->subSecond() : $this->carbon::parse('last day of December '.$year)->addDay()->subSecond();
    }

    /**
     * Builds the amChartData for all years and days.
     */
    protected function setAmChartData(int $year): void
    {
        $period = collect(CarbonPeriod::create($this->begin, 'P1D', $this->end));
        $this->amChartData[$year] = $period->mapWithKeys(function ($date) {
            return [$date->format('Y-m-d') => ['date' => $date->format('Y-m-d')]];
        });
    }

    /**
     * Set yearDaysArray for merging expedition dates and counts.
     */
    protected function setYearDaysArray(int $year): void
    {
        $this->yearDaysArray = collect(array_fill_keys($this->amChartData[$year]->keys()->toArray(), []));
    }

    /**
     * Process expedition and return completed date collections.
     */
    protected function processExpedition(Expedition $expedition, int $year, mixed $dateCount = null): Collection
    {
        $record = 'expedition'.$expedition->id;

        $mappedDates = $this->mapDateCounts($dateCount, $record);
        $mergedDates = $this->yearDaysArray->mergeRecursive($mappedDates);

        $completeDates = $this->addEmptyDateCounts($mergedDates, $record);
        $this->calculateCountTotals($completeDates, $record);

        $this->buildExpeditionSeries($expedition, $year);

        return $completeDates;
    }

    /**
     * Get transcriptions per workflow for the given year.
     */
    protected function transcriptionCountPerDate(int $workflowId): mixed
    {
        return $this->panoptesTranscriptionService->getTranscriptionCountPerDate($workflowId, $this->begin, $this->end);
    }

    /**
     * Map date counts for expedition transcriptions.
     */
    protected function mapDateCounts(Collection $dateCount, string $record): Collection
    {
        return $dateCount->mapWithKeys(function ($value, $key) use ($record) {
            return [$key => [$record => $value]];
        })->reject(function ($value) {
            return empty($value);
        });
    }

    /**
     * Add empty values for missing date fields for expedition.
     */
    protected function addEmptyDateCounts(Collection $mergedDates, string $record): Collection
    {
        return $mergedDates->mapWithKeys(function ($count, $date) use ($record) {
            if (empty($count)) {
                return [$date => [$record => 0]];
            }

            return [$date => $count];
        });
    }

    /**
     * Calculate the count totals per date for Expeditioni.
     */
    protected function calculateCountTotals(Collection &$completeDates, string $record): void
    {
        $total = 0;
        $completeDates->transform(function ($value, $key) use ($record, &$total) {
            $total = $total === 0 ? $value[$record] : $total + $value[$record];

            return [$record => $total];
        });
    }

    /**
     * Build expedition series and add to chart series.
     */
    public function buildExpeditionSeries(Expedition $expedition, int $year): void
    {
        $this->projectChartSeriesFile['dataFields']['valueY'] = 'expedition'.$expedition->id;
        $this->projectChartSeriesFile['name'] = $expedition->title;
        $this->projectChartSeries[$year][] = $this->projectChartSeriesFile;
    }
}
