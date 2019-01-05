<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\AmChart;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Project;
use Carbon\CarbonPeriod;
use File;
use Illuminate\Console\Command;

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
    protected $signature = 'amchart:new {projectId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    protected $projectContract;

    /**
     * @var \App\Repositories\Interfaces\AmChart
     */
    private $chartContract;

    /**
     * @var PanoptesTranscription
     */
    protected $transcription;

    /**
     * @var int
     */
    protected $projectId;

    /**
     * @var
     */
    protected $earliest_date;

    /**
     * @var
     */
    protected $finished_date;

    /**
     * @var mixed
     */
    protected $amChartTemplate;

    /**
     * @var mixed
     */
    protected $amChartSeries;

    /**
     * AmChartNew constructor.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Repositories\Interfaces\AmChart $chartContract
     * @param \App\Repositories\Interfaces\PanoptesTranscription $transcription
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function __construct(
        Project $projectContract,
        AmChart $chartContract,
        PanoptesTranscription $transcription
    )
    {
        parent::__construct();

        $this->projectContract = $projectContract;
        $this->chartContract = $chartContract;
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
        $this->projectId = $this->argument('projectId');
        if ($this->projectId === null) {
            echo 'Project Id required' . PHP_EOL;
            return;
        }

        $relations = ['expeditions.stat', 'expeditions.nfnWorkflow'];

        $time_start = microtime(true);
        $project = $this->projectContract->findWith($this->projectId, $relations);
        echo 'Get project query time in seconds: ' . (microtime(true) - $time_start) . PHP_EOL;

        $time_start = microtime(true);
        $this->earliest_date = $this->transcription->getMinFinishedAtDateByProjectId($project->id);
        echo 'Get earliest date query time in seconds: ' . (microtime(true) - $time_start) . PHP_EOL;

        $time_start = microtime(true);
        $this->finished_date = $this->transcription->getMaxFinishedAtDateByProjectId($project->id);
        echo 'Get finished date query time in seconds: ' . (microtime(true) - $time_start) . PHP_EOL;

        if (null === $this->earliest_date || null === $this->finished_date)
        {
            return;
        }

        $time_start = microtime(true);
        $this->setDateArray();
        echo 'Set date array time in seconds: ' . (microtime(true) - $time_start) . PHP_EOL;

        $project->expeditions->reject(function($expedition){
            return ! isset($expedition->stat->transcriptions_completed) ||
                $expedition->stat->transcriptions_completed === 0 ||
                null !== $expedition->deleted_at;
        })->each(function($expedition){
            $this->processExpedition($expedition);
        });

        $this->amChartTemplate['data'] = array_values($this->amChartTemplate['data']);

        $this->chartContract->updateOrCreate(['project_id' => $this->projectId], ['data' => $this->amChartTemplate]);
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
        $time_start = microtime(true);
        $transcriptCountByDate = $this->transcription
            ->getTranscriptionCountPerDate($expedition->nfnWorkflow->workflow)
            ->pluck('count', '_id');
        echo 'Transcription count by date in seconds: ' . (microtime(true) - $time_start) . PHP_EOL;

        $time_start = microtime(true);
        $dates = $this->setExpeditionDateArray($transcriptCountByDate);
        echo 'setExpeditionDateArray in seconds: ' . (microtime(true) - $time_start) . PHP_EOL;

        $time_start = microtime(true);
        $this->setExpeditionDateAggregate($dates);
        echo 'setExpeditionDateAggregate in seconds: ' . (microtime(true) - $time_start) . PHP_EOL;

        $time_start = microtime(true);
        $this->buildExpeditionData($expedition->id, $dates);
        echo 'buildExpeditionData in seconds: ' . (microtime(true) - $time_start) . PHP_EOL;

        $time_start = microtime(true);
        $this->buildExpeditionSeries($expedition);
        echo 'buildExpeditionSeries in seconds: ' . (microtime(true) - $time_start) . PHP_EOL;
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
}

