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
use MongoCollection;

class BuildAmChartData extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var
     */
    protected $projectId;

    /**
     * @var array
     */
    protected $expeditions = [];

    /**
     * @var array
     */
    protected $transcriptions = [];

    /**
     * BuildAmChartData constructor.
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
     */
    public function handle(Project $repo, AmChart $chart)
    {
        $project = $repo->skipCache()->with(['expeditions.stat'])->find($this->projectId, ['id']);

        foreach ($project->expeditions as $expedition)
        {
            if ( ! isset($expedition->stat->start_date))
            {
                continue;
            }

            // Save for using later when building missing data for json
            $this->expeditions[$expedition->id] = [$expedition->title, $expedition->stat->start_date];

            $resultSet = $this->processExpedition($project->id, $expedition);

            $this->setTranscriptions($resultSet);
        }

        $this->processTranscriptions();

        $content = call_user_func_array('array_merge', $this->transcriptions);

        $chart->updateOrCreate(['project_id' => $project->id, 'data' => json_encode($content)]);
    }

    /**
     * Process each expedition's transcriptions.
     *
     * @param $projectId
     * @param $expedition
     * @return array
     */
    public function processExpedition($projectId, $expedition)
    {
        $results = $this->getData($projectId, $expedition->id);

        $resultSet = $this->processResultData($expedition, $results);

        uasort($resultSet, [$this, 'sort']);

        $this->fixCount($resultSet);

        return $resultSet;
    }

    /**
     * @param $expedition
     * @param $results
     * @return mixed
     */
    private function processResultData($expedition, $results)
    {
        $resultSet = [];
        foreach ($results as $result)
        {
            $year = $result['_id']['year'];
            $month = $result['_id']['month'];
            $day = $result['_id']['day'];
            $date = $year . '-' . $month . '-' . $day;
            $total = $result['total'];

            $day = $this->calculateDay($expedition->stat->start_date, $date);

            $resultSet[$day] = $this->buildResultSet($expedition->id, $expedition->title, $day, $total);
        }

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
     * @param $id
     * @param $title
     * @param $day
     * @param int $total
     * @return array
     */
    private function buildResultSet($id, $title, $day, $total = 0)
    {
        return [
            'expedition' => $id,
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
     * @param $projectId
     * @param $expeditionId
     * @return mixed
     */
    public function getData($projectId, $expeditionId)
    {
        $client = DB::connection('mongodb')->getMongoClient('mongodb');
        $db = $client->selectDB(Config::get('database.connections.mongodb.database'));
        $collection = new MongoCollection($db, 'transcriptions');

        $ops = [
            ['$match' => ['project_id' => $projectId, 'expedition_id' => $expeditionId]],
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
        if ($a['day'] === $b['day']) {
            return 0;
        }

        return ($a['day'] < $b['day']) ? -1 : 1;
    }

    /**
     * Fix count on expeditions by using running total.
     *
     * @param $resultSet
     */
    public function fixCount(&$resultSet)
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
    public function processTranscriptions()
    {
        foreach ($this->transcriptions as $day => $results)
        {
            $this->addMissingTranscriptionResults($results, $day);
        }
    }

    /**
     * @param $results
     * @param $day
     */
    private function addMissingTranscriptionResults($results, $day)
    {
        $missing = array_diff_key($this->expeditions, $results);
        foreach ($missing as $expedition => $values)
        {
            if ($day === 0)
            {
                $this->transcriptions[$day][$expedition] = $this->buildResultSet($expedition, $values[0], $day);
                continue;
            }

            // Get previous values and continue them for this day
            $previous = $this->transcriptions[$day - 1][$expedition];
            $previous['day'] = $day;
            $this->transcriptions[$day][$expedition] = $previous;
        }
    }
}
