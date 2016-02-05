<?php namespace Biospex\Services\Queue;

use Illuminate\Config\Repository as Config;
use Illuminate\Database\DatabaseManager as DB;
use Biospex\Repositories\Contracts\OcrCsv;
use Biospex\Repositories\Contracts\OcrQueue;
use Biospex\Repositories\Contracts\Project;
use Biospex\Services\Process\Ocr;
use MongoCollection;

class OcrProcessBuild extends QueueAbstract
{

    /**
     * @var mixed
     */
    protected $db;

    /**
     * @var OcrQueue
     */
    protected $ocrQueue;

    /**
     * @var Ocr
     */
    protected $ocr;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var OcrCsv
     */
    protected $ocrCsv;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var DB
     */
    protected $databaseManager;

    /**
     * OcrProcessBuild constructor.
     * @param Config $config
     * @param DB $databaseManager
     * @param OcrQueue $ocrQueue
     * @param Ocr $ocr
     * @param OcrCsv $ocrCsv
     * @param Project $project
     */
    public function __construct(
        Config $config,
        DB $databaseManager,
        OcrQueue $ocrQueue,
        Ocr $ocr,
        OcrCsv $ocrCsv,
        Project $project
    )
    {
        $this->config = $config;
        $this->databaseManager = $databaseManager;
        $this->ocrQueue = $ocrQueue;
        $this->ocr = $ocr;
        $this->project = $project;
        $this->ocrCsv = $ocrCsv;

        $this->db = $this->config->get('database.connections.mongodb.database');
    }

    /**
     * Fire the job.
     *
     * Data contains project id and expedition id. If project id exists, then we are
     * processing a DWC import.
     *
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->job = $job;

        if ($this->ocr->disableOcr) {
            $this->delete();

            return;
        }

        $projectId = (int)$data['project_id'];
        $expeditionId = null; // isset($data['expedition_id']) ? (int) $data['expedition_id'] : null;


        if (!$this->checkProjectProcessing($projectId)) {
            $this->delete();

            return;
        }

        $this->buildOcrSubjectsArray($projectId, $expeditionId);

        $data = $this->ocr->getChunkQueueData();

        if (empty($data)) {
            $this->delete();

            return;
        }

        $lastKey = array_search(end($data), $data);
        $ocrCsv = $this->ocrCsv->create(['subjects' => '']);

        foreach ($data as $key => $chunk) {
            $batch = ($key == $lastKey) ? 1 : 0;
            $record = $this->ocr->saveOcrQueue($projectId, $ocrCsv->id, $chunk, count($chunk), $batch);
            $date = $this->ocr->setQueueLaterTime(0);
            $this->ocr->queueLater($date, ['id' => $record->id]);
        }

        $this->delete();

        return;
    }

    /**
     * Query MongoDB and return cursor
     * @param $projectId
     * @param $expeditionId
     * @return \MongoCursor
     */
    protected function setCursor($projectId, $expeditionId)
    {
        $db = $this->databaseManager->connection('mongodb')->getMongoClient()->selectDB($this->db);
        $collection = new MongoCollection($db, 'subjects');

        $query = is_null($expeditionId) ?
            ['project_id' => $projectId, 'ocr' => ''] :
            ['project_id' => $projectId, 'expedition_ids' => $expeditionId, 'ocr' => ''];


        return $collection->find($query);
    }

    /**
     * Check whether we should be processing this ocr request.
     * @param $projectId
     * @return bool|void
     */
    protected function checkProjectProcessing($projectId)
    {
        if (!$this->checkOcrActor($projectId))
            return false;

        $queue = $this->ocrQueue->findByProjectId($projectId);
        if (!is_null($queue))
            return false;

        return true;
    }

    /**
     * Check if project has ocr actor.
     * @param $projectId
     * @return bool
     * @internal param $project
     */
    protected function checkOcrActor($projectId)
    {
        $project = $this->project->findWith($projectId, ['workflow.actors']);

        foreach ($project->workflow->actors as $actor) {
            if (strtolower($actor->title) == 'ocr') {
                return true;
            }
        }

        return false;
    }

    /**
     * Build the ocr subject array
     * @param $projectId
     * @param $expeditionId
     */
    protected function buildOcrSubjectsArray($projectId, $expeditionId)
    {
        $cursor = $this->setCursor($projectId, $expeditionId);

        foreach ($cursor as $id => $doc) {
            $doc['_id'] = $id;
            $subject = array_to_object($doc);
            $this->ocr->buildOcrQueueData($subject);
        }

        return;
    }
}