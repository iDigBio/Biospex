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
    protected $signature = 'amchart:new {projectIds?}';

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
    protected $amChartData;

    /**
     * @var mixed
     */
    protected $amChartSeries;

    /**
     * @var mixed
     */
    protected $amChartSeriesFile;

    /**
     * AmChartNew constructor.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Repositories\Interfaces\AmChart $chartContract
     * @param \App\Repositories\Interfaces\PanoptesTranscription $transcription
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
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectIds = $this->argument('projectIds') === null ?
            $this->chartContract->all(['project_id'])->pluck('project_id') :
            collect(explode(',', $this->argument('projectIds')));

        $projectIds->each(function($projectId) {

            $this->earliest_date = $this->transcription->getMinFinishedAtDateByProjectId($projectId);
            $this->finished_date = $this->transcription->getMaxFinishedAtDateByProjectId($projectId);

            if (null === $this->earliest_date || null === $this->finished_date)
            {
                return;
            }

            $this->resetTemplates();

            $project = $this->projectContract->getProjectForAmChartJob($projectId);

            $this->setDateArray();

            $project->expeditions->each(function($expedition){
                $this->processExpedition($expedition);
            });

            $this->amChartData = array_values($this->amChartData);

            $this->chartContract->updateOrCreate(['project_id' => $projectId], ['series' => $this->amChartSeries, 'data' => $this->amChartData]);

            echo 'Completed project ' . $projectId . PHP_EOL;
        });
        echo 'Done' . PHP_EOL;
    }

    /**
     * Reset the templates for each project.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function resetTemplates() {
        $this->amChartData = [];
        $this->amChartSeries = [];
        $this->amChartSeriesFile = json_decode(File::get(config('config.amchart_series')), true);
    }

    /**
     * Set the date array using earliest and latest finished_at date.
     */
    protected function setDateArray()
    {
        $period = CarbonPeriod::create($this->earliest_date, 'P1D', $this->finished_date);

        foreach ($period as $date) {
            $this->amChartData[$date->format('Y-m-d')] = ['date' => $date->format('Y-m-d')];
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
        $dates = collect(array_flip(array_keys($this->amChartData)))->map(function($val, $date) use($transcriptCountByDate) {
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
        foreach($this->amChartData as $date => $array) {
            $this->amChartData[$date] = array_merge($array, ['expedition'.$id => $dates[$date]]);
        }
    }

    /**
     * Build expedition series and add to chart series.
     *
     * @param $expedition
     */
    public function buildExpeditionSeries($expedition)
    {
        $this->amChartSeriesFile['dataFields']['valueY'] = 'expedition'.$expedition->id;
        $this->amChartSeriesFile['name'] = $expedition->title;
        $this->amChartSeries[] = $this->amChartSeriesFile;
    }
}

