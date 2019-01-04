<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\AmChart;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Project;
use Carbon\CarbonPeriod;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Class AmChartNew
 *
 * @package App\Console\Commands
 */
class AmChartNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amchart:new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var PanoptesTranscription
     */
    protected $transcription;

    /**
     * @var int
     */
    protected $projectId;

    /**
     * Array to hold all transcription results.
     *
     * @var array
     */
    protected $transcriptions = [];

    /**
     * @var
     */
    protected $defaultDates = [];

    /**
     * @var
     */
    protected $earliest_date;

    /**
     * @var
     */
    protected $finished_date;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * @var \App\Repositories\Interfaces\AmChart
     */
    private $chart;

    private $amChartTemplate;

    private $amChartSeries;

    private $total;

    /**
     * AmChartNew constructor.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Repositories\Interfaces\AmChart $chart
     * @param \App\Repositories\Interfaces\PanoptesTranscription $transcription
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function __construct(
        Project $projectContract,
        AmChart $chart,
        PanoptesTranscription $transcription
    )
    {
        parent::__construct();
        $this->projectId = 51;
        $this->projectContract = $projectContract;
        $this->chart = $chart;
        $this->transcription = $transcription;
        $this->amChartTemplate = json_decode(File::get(config('config.amchart_template')), true);
        $this->amChartSeries = json_decode(File::get(config('config.amchart_series')), true);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->transcriptions = [];

        $relations = ['expeditions.stat', 'expeditions.nfnWorkflow'];
        $project = $this->projectContract->findWith($this->projectId, $relations);
        $this->earliest_date = $this->transcription->getMinFinishedAtDateByProjectId($project->id);
        $this->finished_date = $this->transcription->getMaxFinishedAtDateByProjectId($project->id);

        if (null === $this->earliest_date || null === $this->finished_date)
        {
            return;
        }

        $this->setDateArray();

        $this->total = 1;
        $project->expeditions->reject(function($expedition){
            return ! isset($expedition->stat->transcriptions_completed) ||
                $expedition->stat->transcriptions_completed === 0 ||
                null !== $expedition->deleted_at;
        })->each(function($expedition){
            $this->processExpedition($expedition);
        });

        $this->amChartTemplate['data'] = array_values($this->amChartTemplate['data']);
        //Storage::put('test.json', json_encode($this->amChartTemplate));
        //exit;

        $this->chart->updateOrCreate(['project_id' => $this->projectId], ['data' => $this->amChartTemplate]);
    }

    /**
     * Set the date array using earliest and latest finished_at date.
     */
    protected function setDateArray()
    {
        $period = CarbonPeriod::create($this->earliest_date, 'P1D', $this->finished_date);

        foreach ($period as $date) {
            $this->amChartTemplate['data'][$date->format('Y-m-d')] = ['date' => $date->format('Y-m-d')];
        }
    }

    /**
     * Process each expedition's transcriptions.
     *
     * @param $expedition
     */
    public function processExpedition($expedition)
    {
        $transcriptCountByDate = $this->transcription
            ->getTranscriptionCountPerDate($expedition->nfnWorkflow->workflow)
            ->pluck('count', '_id');

        $dates = $this->setExpeditionDateArray($transcriptCountByDate);

        $this->setExpeditionDateAggregate($dates);

        $this->buildExpeditionData($expedition->id, $dates);

        $this->buildExpeditionSeries($expedition);
    }

    /**
     * Set date span and count for Expedition.
     *
     * @param $transcriptCountByDate
     * @return array
     */
    public function setExpeditionDateArray($transcriptCountByDate)
    {
        $dates = collect(array_flip(array_keys($this->amChartTemplate['data'])))->map(function($val, $date) use($transcriptCountByDate) {
            return isset($transcriptCountByDate[$date]) ? $transcriptCountByDate[$date] : 0;
        })->toArray();

        return $dates;
    }

    /**
     * Aggregate the count totals across dates.
     *
     * @param $dates
     */
    public function setExpeditionDateAggregate(&$dates)
    {
        $total = 0;
        foreach ($dates as $date => $count) {
            $total = $total === 0 ? $count : $total + $count;
            $dates[$date] = $total;
        }
    }

    /**
     * Build data array and include missing dates for expedition.
     *
     * @param $id
     * @param array $dates
     */
    public function buildExpeditionData($id, $dates)
    {
        foreach($this->amChartTemplate['data'] as $date => $array) {
            $this->amChartTemplate['data'][$date] = array_merge($array, ['expedition'.$id => $dates[$date]]);
        }
    }

    /**
     * Build expedition series and add to chart series.
     *
     * @param $expedition
     */
    public function buildExpeditionSeries($expedition)
    {
        $this->amChartSeries['dataFields']['valueY'] = 'expedition'.$expedition->id;
        $this->amChartSeries['name'] = $expedition->title;
        $this->amChartTemplate['series'][] = $this->amChartSeries;
    }


    /**
     * Build missing data for days in expedition.
     *
     * @param $daysArray
     */
    protected function buildMissingData(&$daysArray)
    {
        foreach ($daysArray as $day => $data)
        {
            if (! isset($data['count']))
            {
                $daysArray[$day]['count'] = 0;
            }
        }
    }

    /**
     * Fix count on expeditions by using running total.
     *
     * @param $daysArray
     */
    public function aggregateResultCount(&$daysArray)
    {
        $total = 0;
        foreach ($daysArray as $day => $array)
        {
            $total += $array['count'];
            $daysArray[$day]['count'] = $total;
        }
    }

    /**
     * Add to transcriptions array using designated keys.
     *
     * @param $results
     */
    public function setTranscriptions($results)
    {
        foreach($this->transcriptions as $day => $valueArray) {
            $this->transcriptions[$day] = array_merge($this->transcriptions[$day], $results[$day]);
        }
    }

    /**
     * Calculate the number of days.
     *
     * @param $startDate
     * @param $finishedDate
     * @return string
     */
    public function calculateDay($startDate, $finishedDate)
    {
        $startTime = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $finishTime = Carbon::createFromFormat('Y-m-d', $finishedDate)->startOfDay();

        return $finishTime->diff($startTime)->format('%a');
    }

    /**
     * Sort by day and expedition.
     *
     * @param $a
     * @param $b
     * @return mixed
     */
    public function sort($a, $b)
    {
        return ($a['day'] - $b['day']) ?: $a['expedition'] - $b['expedition'];
    }
}

