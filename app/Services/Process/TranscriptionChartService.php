<?php declare(strict_types=1);

namespace App\Services\Process;

use App\Models\Expedition;
use App\Repositories\Interfaces\AmChart;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Project;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class TranscriptionChartService
 * For each expedition, need to find counts per day for span of the year.
 * These will be added to [year] series and [year] data.
 * $year = Carbon::now()->year;
 * $beginYear = Carbon::parse($year . '-01-01');
 * $endYear = Carbon::parse($year . '-12-31');
 * Carbon::parse('first day of January next year')->subSecond();
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
     * @var \App\Repositories\Interfaces\AmChart
     */
    private $amChartContract;

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
     * @param \App\Repositories\Interfaces\AmChart $amChartContract
     * @param \App\Repositories\Interfaces\PanoptesTranscription $transcriptionContract
     */
    public function __construct(
        Project $projectContract,
        AmChart $amChartContract,
        PanoptesTranscription $transcriptionContract
    ) {

        $this->projectContract = $projectContract;
        $this->amChartContract = $amChartContract;
        $this->transcriptionContract = $transcriptionContract;
    }

    /**
     * Process project for amchart.
     *
     * @param \App\Models\Project $project
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function process(\App\Models\Project $project)
    {
        $this->resetTemplates();

        $year = Carbon::now()->year;

        $this->buildCompleteChartData($project);

        $this->amChartData->transform(function($value, $key) {
            return array_values($value->toArray());
        });

        $data = ['series' => $this->projectChartSeries, 'data' => $this->amChartData];
        $this->amChartContract->updateOrCreate(['project_id' => $project->id], $data);

        //$this->writeToFile('amchartData.json', json_encode($this->amChartData));
        //exit;


        return;

        //$this->resetTemplates();
        /*
        $this->setDateArray();

        $project->expeditions->each(function ($expedition) use ($transcriptionContract) {
            $this->processExpedition($expedition, $transcriptionContract);
        });

        $this->amChartData = array_values($this->amChartData);

        $data = ['series' => $this->projectChartSeries, 'data' => $this->amChartData];
        $amChart = $amChartContract->updateOrCreate(['project_id' => $project->id], $data);

        AmChartImageJob::dispatch($amChart);
        */
    }

    /**
     * Reset the templates for each project.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function resetTemplates()
    {
        $this->amChartData = collect();
        $this->projectChartSeries = [];
        $this->projectChartSeriesFile = json_decode(\File::get(config('config.project_chart_series')), true);
    }

    /**
     * Build complete series and data for chart for all years.
     *
     * @param $project
     */
    protected function buildCompleteChartData($project)
    {
        $years = $this->setYearsArray($project->id);
        if ($years === null) {
            return;
        }

        $years->each(function ($year) use ($project) {
            $this->setBeginEndOfYear($year);
            $this->setAmChartData($year);
            $this->setYearDaysArray($year);

            $project->expeditions->each(function ($expedition) use ($year) {
                $completeDates = $this->processExpedition($expedition, $year);

                $this->amChartData[$year] = $this->amChartData[$year]->mergeRecursive($completeDates);
            });
        });
    }

    /**
     * Set years array.
     * Carbon::parse('first day of January next year')->subSecond();
     *
     * @param int $projectId
     * @return \Illuminate\Support\Collection|null
     */
    protected function setYearsArray(int $projectId)
    {
        $earliest_date = $this->transcriptionContract->getMinFinishedAtDateByProjectId($projectId);
        $latest_date = $this->transcriptionContract->getMaxFinishedAtDateByProjectId($projectId);

        if (null === $earliest_date || null === $latest_date) {
            return null;
        }

        return \DateHelper::getRangeInYearsDesc($earliest_date, $latest_date);
    }

    /**
     * Return first day and last day of given year.
     *
     * @param int $year
     */
    protected function setBeginEndOfYear(int $year)
    {
        $this->begin = Carbon::parse('first day of January '.$year);
        $this->end = Carbon::parse('last day of December '.$year)->addDay()->subSecond();
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