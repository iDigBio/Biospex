<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\AmChart;
use Carbon\CarbonPeriod;
use File;

class AmChartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * @var PanoptesTranscription
     */
    protected $transcriptionContract;

    /**
     * @var
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
    protected $amChartData;

    /**
     * @var mixed
     */
    protected $projectChartSeries;

    /**
     * @var mixed
     */
    protected $projectChartSeriesFile;

    /**
     * AmChartJob constructor.
     *
     * @param int $projectId
     */
    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->onQueue(config('config.chart_tube'));
    }

    /**
     * @param Project $projectContract
     * @param AmChart $amChartContract
     * @param PanoptesTranscription $transcriptionContract
     */
    public function handle(
        Project $projectContract,
        AmChart $amChartContract,
        PanoptesTranscription $transcriptionContract
    ) {

        $this->earliest_date = $transcriptionContract->getMinFinishedAtDateByProjectId($this->projectId);
        $this->finished_date = $transcriptionContract->getMaxFinishedAtDateByProjectId($this->projectId);

        if (null === $this->earliest_date || null === $this->finished_date) {
            return;
        }

        $this->resetTemplates();

        $project = $projectContract->getProjectForAmChartJob($this->projectId);

        $this->setDateArray();

        $project->expeditions->each(function ($expedition) use ($transcriptionContract) {
            $this->processExpedition($expedition, $transcriptionContract);
        });

        $this->amChartData = array_values($this->amChartData);

        $data = ['series' => $this->projectChartSeries, 'data' => $this->amChartData];
        $amChartContract->updateOrCreate(['project_id' => $project->id], $data);

        $this->delete();
    }

    /**
     * Reset the templates for each project.
     */
    protected function resetTemplates() {
        $this->amChartData = [];
        $this->projectChartSeries = [];
        $this->projectChartSeriesFile = json_decode(File::get(config('config.amchart_series')), true);
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
     * @param $transcriptionContract PanoptesTranscription
     */
    public function processExpedition($expedition, $transcriptionContract)
    {

        $transcriptCountByDate = $transcriptionContract
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
        $this->projectChartSeriesFile['dataFields']['valueY'] = 'expedition'.$expedition->id;
        $this->projectChartSeriesFile['name'] = $expedition->title;
        $this->projectChartSeries[] = $this->projectChartSeriesFile;
    }
}
