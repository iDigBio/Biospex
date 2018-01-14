<?php

namespace App\Jobs;

use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Interfaces\PanoptesTranscription;
use App\Interfaces\Project;
use App\Interfaces\AmChart;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AmChartJob extends Job implements ShouldQueue
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
    protected $transcriptions;

    /**
     * @var
     */
    protected $defaultDays;

    /**
     * @var
     */
    protected $earliest_date;

    /**
     * @var
     */
    protected $latest_date;

    /**
     * AmChartJob constructor.
     * @param int $projectId
     */
    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->onQueue(config('config.beanstalkd.chart'));
    }

    /**
     * @param Project $projectContract
     * @param AmChart $chart
     * @param PanoptesTranscription $transcription
     */
    public function handle(
        Project $projectContract,
        AmChart $chart,
        PanoptesTranscription $transcription
    )
    {
        $this->transcriptions = [];
        $this->transcription = $transcription;

        $relations = ['expeditions.stat', 'expeditions.nfnWorkflow'];
        $project = $projectContract->findWith($this->projectId, $relations);
        $earliest_date = $this->transcription->getMinFinishedAtDateByProjectId($project->id);
        $finished_date = $this->transcription->getMaxFinishedAtDateByProjectId($project->id);

        if (null === $earliest_date || null === $finished_date)
        {
            $this->delete();

            return;
        }

        $this->setDaysAndDates($earliest_date, $finished_date);

        $this->processProjectExpeditions($project->expeditions);

        uasort($this->transcriptions, [$this, 'sort']);

        $content = array_values($this->transcriptions);

        $chart->updateOrCreate(['project_id' => $this->projectId], ['data' => json_encode($content)]);

        $this->delete();
    }

    /**
     * Set the days array using earliest and latest finished_at date.
     *
     * @param $earliest_date
     * @param $latest_date
     */
    protected function setDaysAndDates($earliest_date, $latest_date)
    {
        $this->earliest_date = $earliest_date;
        $this->latest_date = $latest_date;

        $total = $this->calculateDay($earliest_date, $latest_date);

        $i = 0;
        while ($i <= $total) {
            $this->defaultDays[$i] = '';
            $i++;
        }
    }

    /**
     * Process a project's expeditions.
     *
     * @param array $expeditions
     */
    protected function processProjectExpeditions($expeditions)
    {
        foreach ($expeditions as $expedition)
        {
            if ( ! isset($expedition->stat->transcriptions_completed)
                || $expedition->stat->transcriptions_completed === 0
                || null !== $expedition->deleted_at)
            {
                continue;
            }

            $resultSet = $this->processExpedition($expedition);

            $this->aggregateResultCount($resultSet);

            $this->setTranscriptions($resultSet);
        }
    }

    /**
     * Process each expedition's transcriptions.
     *
     * @param $expedition
     * @return mixed
     */
    public function processExpedition($expedition)
    {
        $transcriptCountByDate = $this->transcription
            ->getTranscriptionCountPerDate($expedition->nfnWorkflow->workflow);

        $daysArray = $this->processTranscriptionDateCounts($expedition, $transcriptCountByDate);

        return $this->buildMissingData($expedition, $daysArray);
    }

    /**
     * Process transcript data.
     *
     * @param $expedition
     * @param Collection $transcriptCountByDate
     * @return array
     */
    protected function processTranscriptionDateCounts($expedition, $transcriptCountByDate)
    {
        $daysArray = $this->defaultDays;

        foreach ($transcriptCountByDate as $date)
        {
            $day = $this->calculateDay($this->earliest_date, $date['_id']);
            $daysArray[$day] = $this->buildResultSet($expedition->id, $expedition->title, $day, $date['count']);
        }

        return $daysArray;
    }

    /**
     * Build missing data for days in expedition.
     *
     * @param $expedition
     * @param array $daysArray
     * @return mixed
     */
    protected function buildMissingData($expedition, $daysArray)
    {
        foreach ($daysArray as $day => &$data)
        {
            if ($data === '')
            {
                $data = $this->buildResultSet($expedition->id, $expedition->title, $day);
            }
        }

        unset($data);

        return $daysArray;
    }

    /**
     * Fix count on expeditions by using running total.
     *
     * @param $resultSet
     */
    public function aggregateResultCount(&$resultSet)
    {
        $total = 0;
        foreach ($resultSet as $key => $value)
        {
            $total += $resultSet[$key]['count'];
            $resultSet[$key]['count'] = $total;
        }
    }

    public function imitateMerge(&$array1, &$array2) {
        foreach($array2 as $i) {
            $array1[] = $i;
        }
    }

    /**
     * Add to transcriptions array using designated keys.
     *
     * @param $results
     */
    public function setTranscriptions($results)
    {
        $this->transcriptions = array_merge($this->transcriptions, $results);
    }

    /**
     * Build result set into proper format for am chart.
     *
     * @param $expeditionId
     * @param $title
     * @param $day
     * @param int $total
     * @return array
     */
    protected function buildResultSet($expeditionId, $title, $day, $total = 0)
    {
        return [
            'expedition' => (int) $expeditionId,
            'collection' => $title,
            'count'      => $total,
            'day'        => (int) $day
        ];
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
