<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Repositories\Contracts\AmChart;
use App\Repositories\Contracts\Project;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoCollection;

class BuildAmChartData extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * Id of project being processed.
     *
     * @var
     */
    protected $id;

    /**
     * Hold ids of expeditions for comparison with results.
     *
     * @var array
     */
    protected $expeditions = [];

    /**
     * Array to hold all transcription results.
     *
     * @var array
     */
    protected $transcriptions = [];

    /**
     * BuildAmChartData constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the Job.
     *
     * @param Project $repo
     * @param AmChart $chart
     */
    public function handle(Project $repo, AmChart $chart)
    {
        $project = $repo->skipCache()->with(['expeditions.stat'])->find($this->id, ['id']);

        $this->processProject($project);

        $this->fixMissingTranscriptionDays();

        $this->fixMissingResultsInTranscriptions();

        $content = call_user_func_array('array_merge', $this->transcriptions);

        $chart->updateOrCreate(['project_id' => $this->id], ['data' => json_encode($content)]);
    }

    /**
     * @param $project
     */
    protected function processProject($project)
    {
        foreach ($project->expeditions as $expedition)
        {
            if ( ! isset($expedition->stat->start_date))
            {
                continue;
            }

            $resultSet = $this->processExpedition($expedition);

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
        $this->expeditions[$expedition->id] = [$expedition->title, $expedition->stat->start_date];

        $results = $this->getData($expedition->id);

        $resultSet = $this->processResultData($expedition, $results);

        $this->aggregateResultCount($resultSet);

        return $resultSet;
    }

    /**
     * @param $expedition
     * @param $results
     * @return mixed
     */
    protected function processResultData($expedition, $results)
    {
        $resultSet = [];
        foreach ($results as $result)
        {
            $date = $result['_id']['year'] . '-' . $result['_id']['month'] . '-' . $result['_id']['day'];
            $total = $result['total'];
            $day = $this->calculateDay($expedition->stat->start_date, $date);

            $resultSet[$day] = $this->buildResultSet($expedition->id, $expedition->title, $day, $total);
        }

        if ( ! array_key_exists(0, $resultSet))
        {
            $resultSet[0] = $this->buildResultSet($expedition->id, $expedition->title, 0, 0);
        }

        uasort($resultSet, [$this, 'sort']);

        return $resultSet;
    }

    /**
     * Add to transcriptions array using designated keys.
     *
     * @param $results
     */
    public function setTranscriptions($results)
    {
        foreach ($results as $key => $item)
        {
            $this->transcriptions[$key][$item['expedition']] = $item;
        }
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
     * Calculate the day using start date.
     *
     * @param $startDate
     * @param $finishedDate
     * @return string
     */
    public function calculateDay($startDate, $finishedDate)
    {
        $start = new \DateTime($startDate);
        $finish = new \DateTime($finishedDate);

        return $finish->diff($start)->format('%a');
    }

    /**
     * Get data from MongoDB Transcriptions.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function getData($expeditionId)
    {
        $client = DB::connection('mongodb')->getMongoClient('mongodb');
        $db = $client->selectDB(Config::get('database.connections.mongodb.database'));
        $collection = new MongoCollection($db, 'transcriptions');

        $ops = [
            ['$match' => ['project_id' => $this->id, 'expedition_id' => $expeditionId]],
            [
                '$group' => [
                    '_id'   => [
                        'year'  => ['$year' => '$finished_at'],
                        'month' => ['$month' => '$finished_at'],
                        'day'   => ['$dayOfMonth' => '$finished_at'],
                        //'hour'  => ['$hour' => '$finished_at']
                    ],
                    'total' => ['$sum' => 1]
                ],
            ]
        ];

        $results = $collection->aggregate($ops);

        return $results['result'];
    }

    /**
     * Sort by date.
     *
     * @param $a
     * @param $b
     * @return int
     */
    public function sort($a, $b)
    {
        if ($a['day'] === $b['day'])
        {
            return 0;
        }

        return ($a['day'] < $b['day']) ? -1 : 1;
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
     * Loop through transcriptions array and fill in missing pieces for the charting.
     */
    public function fixMissingTranscriptionDays()
    {
        $min = 0;
        $max = max(array_keys($this->transcriptions));
        $this->transcriptions += array_fill_keys(range($min, $max), '');
        ksort($this->transcriptions);

        foreach ($this->transcriptions as $day => $results)
        {
            if ( ! is_array($results))
            {
                $this->findPreviousTranscription($day);
            }
        }

        ksort($this->transcriptions);
    }

    /**
     * Fix missing results inside each day.
     */
    protected function fixMissingResultsInTranscriptions()
    {
        foreach ($this->transcriptions as $day => $results)
        {
            $missing = array_diff_key($this->expeditions, $results);
            if (count($missing) > 0)
            {
                $this->processMissingResults($missing, $day);
            }
        }
    }

    /**
     * Find previous day with results and copy forward.
     *
     * @param $day
     * @return mixed
     */
    protected function findPreviousTranscription($day)
    {
        $i = $day;
        while ($day >= 0)
        {
            if (is_array($this->transcriptions[$day]))
            {
                $results = $this->transcriptions[$day];
                $this->transcriptions[$i] = $this->fixDay($results, $day);

                break;
            }
            $day--;
        }
    }

    /**
     * @param $missing
     * @param $day
     */
    protected function processMissingResults($missing, $day)
    {
        foreach ($missing as $expedition => $value)
        {
            $this->findPreviousResult($day, $expedition);
        }
    }

    /**
     * Find previous result to cary forward in current day.
     *
     * @param $day
     * @param $expedition
     */
    protected function findPreviousResult($day, $expedition)
    {
        $i = $day;
        while ($day >= 0)
        {
            if (array_key_exists($expedition, $this->transcriptions[$day]))
            {
                $result = $this->transcriptions[$day][$expedition];
                $result['day'] = $day + 1;
                $this->transcriptions[$i][$expedition] = $result;

                break;
            }
            $day--;
        }
    }

    /**
     * @param $results
     * @param $day
     * @return mixed
     */
    protected function fixDay($results, $day)
    {
        foreach ($results as $key => $result)
        {
            $results[$key]['day'] = $day + 1;
        }
        return $results;
    }
}
