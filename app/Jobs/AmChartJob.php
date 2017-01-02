<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Repositories\Contracts\AmChart;
use App\Repositories\Contracts\NfnClassification;
use App\Repositories\Contracts\Project;
use Carbon\Carbon;

class AmChartJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * Id of project being processed.
     *
     * @var
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
    protected $classification;

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
     * @param $projectId
     */
    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * Execute the Job.
     *
     * @param Project $repo
     * @param AmChart $chart
     * @param NfnClassification $classification
     */
    public function handle(Project $repo, AmChart $chart, NfnClassification $classification)
    {
        $this->classification = $classification;

        $project = $this->getProject($repo);

        $this->setDaysAndDates($project->earliest_finished_at_date, $project->latest_finished_at_date);

        $this->processProjectExpeditions($project->expeditions);

        uasort($this->transcriptions, [$this, 'sort']);

        $content = array_values($this->transcriptions);

        $chart->updateOrCreate(['project_id' => $this->projectId], ['data' => json_encode($content)]);
    }

    /**
     * @param Project $repo
     * @return mixed
     */
    protected function getProject(Project $repo)
    {
        $with = [
            'expeditions.stat',
            'expeditions.nfnWorkflow',
            'classificationsEarliestFinishedAtDate',
            'classificationsLatestFinishedAtDate'
        ];

        return $repo->skipCache()->with($with)->find($this->projectId);
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
            echo $i++;
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
            if ($expedition->stat->transcriptions_completed === 0 || null !== $expedition->deleted_at)
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
     * @return mixed|void
     */
    public function processExpedition($expedition)
    {
        $classificationCounts = $this->retrieveClassificationCount($expedition->nfnWorkflow->id);

        $daysArray = $this->processClassificationData($expedition, $classificationCounts);

        return $this->buildMissingData($expedition, $daysArray);
    }

    /**
     * Process classification data.
     *
     * @param $expedition
     * @param array $classificationCounts
     * @return array
     */
    protected function processClassificationData($expedition, $classificationCounts)
    {
        $daysArray = $this->defaultDays;

        foreach ($classificationCounts as $classification)
        {
            $day = $this->calculateDay($this->earliest_date, $classification->finished_at);
            $daysArray[$day] = $this->buildResultSet($expedition->id, $expedition->title, $day, $classification->total);
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
     * Get data from nfn_classifications.
     *
     * @param $workflow
     * @return mixed
     */
    public function retrieveClassificationCount($workflow)
    {
        return $this->classification->skipCache()->getExpeditionsGroupByFinishedAt($workflow);
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
        return $a['day'] - $b['day'] ?: $a['expedition'] - $b['expedition'];
    }
}
