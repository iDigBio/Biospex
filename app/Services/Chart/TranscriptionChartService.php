<?php declare(strict_types=1);
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
use App\Repositories\PanoptesTranscriptionRepository;
use Carbon\CarbonPeriod;
use File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class TranscriptionChartService
 *
 * @package App\Services\Process
 */
class TranscriptionChartService
{
    /**
     * @var \App\Repositories\PanoptesTranscriptionRepository
     */
    private $panoptesTranscriptionRepo;

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

    /**
     * @var
     */
    private $begin;

    /**
     * @var
     */
    private $end;

    /**
     * @var
     */
    private $yearDaysArray;

    /**
     * TranscriptionChartService constructor.
     *
     * @param \App\Repositories\PanoptesTranscriptionRepository $panoptesTranscriptionRepo
     */
    public function __construct(
        PanoptesTranscriptionRepository $panoptesTranscriptionRepo
    ) {
        $this->panoptesTranscriptionRepo = $panoptesTranscriptionRepo;
    }

    /**
     * Process project for amchart.
     *
     * @param \App\Models\Project $project
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
     *
     * @param \App\Models\Project $project
     */
    protected function checkNewChart(Project &$project)
    {
        if ($project->amChart === null) {
            $amChart = new AmChart();
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
     *
     * @param $project
     * @param $year
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
     * @param int $projectId
     * @return \Illuminate\Support\Collection|null
     */
    public function setYearsArray(int $projectId)
    {
        $earliest_date = $this->panoptesTranscriptionRepo->getMinFinishedAtDateByProjectId($projectId);
        $latest_date = $this->panoptesTranscriptionRepo->getMaxFinishedAtDateByProjectId($projectId);

        if (null === $earliest_date || null === $latest_date) {
            return null;
        }

        $years = range(Carbon::parse($earliest_date)->year, Carbon::parse($latest_date)->year);
        rsort($years);

        return collect($years);
    }

    /**
     * Return first day and last day of given year, or if current year, get today.
     *
     * @param int $year
     */
    protected function setBeginEndOfYear(int $year)
    {
        $this->begin = Carbon::parse('first day of January '.$year);
        $this->end = $year === Carbon::now()->year ? Carbon::parse('today')->addDay()->subSecond() : Carbon::parse('last day of December '.$year)->addDay()->subSecond();
    }

    /**
     * Builds the amChartData for all years and days.
     *
     * @param int $year
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
     *
     * @param $year
     */
    protected function setYearDaysArray(int $year)
    {
        $this->yearDaysArray = collect(array_fill_keys($this->amChartData[$year]->keys()->toArray(), []));
    }

    /**
     * Process expedition and return completed date collections.
     *
     * @param \App\Models\Expedition $expedition
     * @param int $year
     * @return \Illuminate\Support\Collection
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
     *
     * @param int $workflowId
     * @return mixed
     */
    protected function transcriptionCountPerDate(int $workflowId): mixed
    {
        return $this->panoptesTranscriptionRepo->getTranscriptionCountPerDate($workflowId, $this->begin, $this->end);
    }

    /**
     * Map date counts for expedition transcriptions.
     *
     * @param \Illuminate\Support\Collection $dateCount
     * @param string $record
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
     * @param \Illuminate\Support\Collection $mergedDates
     * @param string $record
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
     *
     * @param \Illuminate\Support\Collection $completeDates
     * @param string $record
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
     *
     * @param \App\Models\Expedition $expedition
     * @param int $year
     */
    public function buildExpeditionSeries(Expedition $expedition, int $year)
    {
        $this->projectChartSeriesFile['dataFields']['valueY'] = 'expedition'.$expedition->id;
        $this->projectChartSeriesFile['name'] = $expedition->title;
        $this->projectChartSeries[$year][] = $this->projectChartSeriesFile;
    }
}