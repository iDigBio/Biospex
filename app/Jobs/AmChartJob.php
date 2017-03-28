<?php

namespace App\Jobs;

use App\Repositories\Contracts\PanoptesTranscriptionContract;
use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\AmChartContract;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;

class AmChartJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * @var PanoptesTranscriptionContract
     */
    protected $transcription;

    /**
     * @var array
     */
    protected $ids;

    /**
     * Array to hold all transcription results.
     *
     * @var array
     */
    protected $transcriptions = [];

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
     * @param array $ids
     */
    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }

    /**
     * @param ProjectContract $projectContract
     * @param AmChartContract $chart
     * @param PanoptesTranscriptionContract $transcription
     */
    public function handle(
        ProjectContract $projectContract,
        AmChartContract $chart,
        PanoptesTranscriptionContract $transcription)
    {
        if (count($this->ids) === 0)
        {
            $this->delete();

            return;
        }

        foreach ($this->ids as $id)
        {
            $this->transcription = $transcription;

            $relations = ['expeditions.stat', 'expeditions.nfnWorkflow'];
            $project = $projectContract->setCacheLifetime(0)->findWithRelations($id, $relations);
            $earliest_date = $this->transcription->setCacheLifetime(0)->getMinFinishedAtDateByProjectId($project->id);
            $finished_date = $this->transcription->setCacheLifetime(0)->getMaxFinishedAtDateByProjectId($project->id);

            if (null === $earliest_date || null === $finished_date)
            {
                $this->delete();

                return;
            }

            $this->setDaysAndDates($earliest_date, $finished_date);

            $this->processProjectExpeditions($project->expeditions);

            uasort($this->transcriptions, [$this, 'sort']);

            $content = array_values($this->transcriptions);

            $chart->updateOrCreateRecord(['project_id' => $id], ['data' => json_encode($content)]);
        }
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
            ->setCacheLifetime(0)
            ->getTranscriptionCountPerDate($expedition->nfnWorkflow->workflow);

        $daysArray = $this->processTranscriptionDateCounts($expedition, $transcriptCountByDate['result']);

        return $this->buildMissingData($expedition, $daysArray);
    }

    /**
     * Process transcript data.
     *
     * @param $expedition
     * @param array $transcriptCountByDate
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
     * @param $id
     * @param $title
     * @param $day
     * @param int $total
     * @return array
     */
    protected function buildResultSet($id, $title, $day, $total = 0)
    {
        return [
            'expedition' => (int) $id,
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
