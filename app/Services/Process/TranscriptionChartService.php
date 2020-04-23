<?php declare(strict_types=1);

namespace App\Services\Process;

use App\Models\AmChart;
use App\Models\Expedition;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Project;
use Carbon\CarbonPeriod;
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
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * @var \App\Repositories\Interfaces\PanoptesTranscription
     */
    private $transcriptionContract;

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
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Repositories\Interfaces\PanoptesTranscription $transcriptionContract
     */
    public function __construct(
        Project $projectContract,
        PanoptesTranscription $transcriptionContract
    ) {

        $this->projectContract = $projectContract;
        $this->transcriptionContract = $transcriptionContract;
    }

    /**
     * Process project for amchart.
     *
     * @param \App\Models\Project $project
     */
    public function process(\App\Models\Project $project)
    {
        $this->checkNewChart($project);
        if($project->amChart === null) {
            return;
        }

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

        $project->amChart->data = array_replace($project->amChart->data, $this->amChartData->toArray());
        $project->amChart->series = array_replace($project->amChart->series, $this->projectChartSeries);

        $project->amChart->save();

        return;
    }

    /**
     * Check if this is a new chart. Create and load if new.
     *
     * @param \App\Models\Project $project
     */
    protected function checkNewChart(\App\Models\Project &$project)
    {
        if ($project->amChart === null) {
            $amChart = new AmChart();
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
        $this->projectChartSeriesFile = json_decode(\File::get($file), true);
    }

    /**
     * Build complete series and data for chart for year.
     *
     * @param $project
     * @param $year
     */
    protected function buildCompleteChartData($project, $year)
    {
        $project->expeditions->each(function ($expedition) use ($year) {
            $completeDates = $this->processExpedition($expedition, $year);

            $this->amChartData[$year] = $this->amChartData[$year]->mergeRecursive($completeDates);
        });
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
        $earliest_date = $this->transcriptionContract->getMinFinishedAtDateByProjectId($projectId);
        $latest_date = $this->transcriptionContract->getMaxFinishedAtDateByProjectId($projectId);

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
     * @return \Illuminate\Support\Collection|void
     */
    protected function processExpedition(Expedition $expedition, int $year)
    {
        $record = 'expedition'.$expedition->id;

        $dateCount = $this->transcriptionCountPerDate($expedition->panoptesProject->panoptes_workflow_id);
        if ($dateCount->isEmpty()) {
            return;
        }

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
    protected function transcriptionCountPerDate(int $workflowId)
    {
        return $this->transcriptionContract->getTranscriptionCountPerDate($workflowId, $this->begin, $this->end);
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