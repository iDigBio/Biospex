<?php

/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Chart;

use App\Models\AmChart;
use App\Models\Expedition;
use App\Models\Project;
use App\Services\Models\PanoptesTranscriptionModelService;
use Carbon\CarbonPeriod;
use File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class TranscriptionChartService
 */
class TranscriptionChartService
{
    /**
     * @var mixed
     */
    private $amChartData;

    /**
     * @var mixed
     */
    private $projectChartSeries;

    /**
     * @var mixed
     */
    private $projectChartSeriesFile;

    private $begin;

    private $end;

    private $yearDaysArray;

    /**
     * TranscriptionChartService constructor.
     */
    public function __construct(private PanoptesTranscriptionModelService $panoptesTranscriptionModelService
    ) {}

    /**
     * Process project for amchart.
     */
    public function process(Project $project)
    {
        $this->checkNewChart($project);

        $this->resetTemplates();

        $years = $this->setYearsArray($project->id);
        if ($years === null) {
            return;
        }

        $years->reject(function ($year) use ($project) {
            return isset($project->amChart['series'][$year]) && $year !== Carbon::now()->year;
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
    protected function checkNewChart(Project &$project)
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
    protected function resetTemplates()
    {
        $this->amChartData = collect();
        $this->projectChartSeries = [];
        $file = config('config.project_chart_series');
        $this->projectChartSeriesFile = json_decode(File::get($file), true);
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
     * Carbon::parse('first day of January next year')->subSecond();
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function setYearsArray(int $projectId)
    {
        $earliest_date = $this->panoptesTranscriptionModelService->getMinFinishedAtDateByProjectId($projectId);
        $latest_date = $this->panoptesTranscriptionModelService->getMaxFinishedAtDateByProjectId($projectId);

        if ($earliest_date === null || $latest_date === null) {
            return null;
        }

        $years = range(Carbon::parse($earliest_date)->year, Carbon::parse($latest_date)->year);
        rsort($years);

        return collect($years);
    }

    /**
     * Return first day and last day of given year, or if current year, get today.
     */
    protected function setBeginEndOfYear(int $year)
    {
        $this->begin = Carbon::parse('first day of January '.$year);
        $this->end = $year === Carbon::now()->year ? Carbon::parse('today')->addDay()->subSecond() : Carbon::parse('last day of December '.$year)->addDay()->subSecond();
    }

    /**
     * Builds the amChartData for all years and days.
     */
    protected function setAmChartData(int $year)
    {
        $period = collect(CarbonPeriod::create($this->begin, 'P1D', $this->end));
        $this->amChartData[$year] = $period->mapWithKeys(function ($date) {
            return [$date->format('Y-m-d') => ['date' => $date->format('Y-m-d')]];
        });
    }

    /**
     * Set yearDaysArray for merging expedition dates and counts.
     */
    protected function setYearDaysArray(int $year)
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
        return $this->panoptesTranscriptionModelService->getTranscriptionCountPerDate($workflowId, $this->begin, $this->end);
    }

    /**
     * Map date counts for expedition transcriptions.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function mapDateCounts(Collection $dateCount, string $record)
    {
        return $dateCount->mapWithKeys(function ($value, $key) use ($record) {
            return [$key => [$record => $value]];
        })->reject(function ($value) {
            return empty($value);
        });
    }

    /**
     * Add empty values for missing date fields for expedition.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function addEmptyDateCounts(Collection $mergedDates, string $record)
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
    protected function calculateCountTotals(Collection &$completeDates, string $record)
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
    public function buildExpeditionSeries(Expedition $expedition, int $year)
    {
        $this->projectChartSeriesFile['dataFields']['valueY'] = 'expedition'.$expedition->id;
        $this->projectChartSeriesFile['name'] = $expedition->title;
        $this->projectChartSeries[$year][] = $this->projectChartSeriesFile;
    }
}
