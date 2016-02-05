<?php

namespace Biospex\Services\Process;

use Illuminate\Config\Repository as Config;
use Biospex\Repositories\Contracts\OcrQueue;
use Biospex\Repositories\Contracts\Subject;
use Illuminate\Contracts\Queue\Queue;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Exception;

class Ocr
{

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Subject
     */
    protected $subject;

    /**
     * @var string
     */
    protected $ocrProcessQueue;

    /**
     * @var mixed
     */
    public $ocrCrop;

    /**
     * @var mixed
     */
    public $ocrChunk;

    /**
     * @var
     */
    public $currentDelayMinutes = 0;

    /**
     * @var mixed
     */
    public $disableOcr;

    /**
     * @var mixed
     */
    public $ocrTube;

    /**
     * @var mixed
     */
    public $ocrPostUrl;

    /**
     * @var mixed
     */
    public $ocrGetUrl;

    /**
     * @var
     */
    public $ocrData;

    /**
     * @var OcrQueue
     */
    private $ocrQueue;

    /**
     * Ocr constructor.
     * @param Config $config
     * @param OcrQueue $ocrQueue
     * @param Subject $subject
     * @param Queue $queue
     */
    public function __Construct(
        Config $config,
        OcrQueue $ocrQueue,
        Subject $subject,
        Queue $queue
    )
    {
        $this->client = new Client();
        $this->config = $config;
        $this->queue = $queue;
        $this->subject = $subject;
        $this->ocrQueue = $ocrQueue;

        $this->ocrProcessQueue = 'Biospex\Services\Queue\OcrProcessQueue';
        $this->ocrCrop = $config->get('config.ocr_crop');
        $this->ocrChunk = $config->get('config.ocr_chunk');
        $this->disableOcr = $config->get('config.disable_ocr');
        $this->ocrTube = $config->get('config.beanstalkd.ocr');
        $this->ocrPostUrl = $config->get('config.ocr_post_url');
        $this->ocrGetUrl = $config->get('config.ocr_get_url');
    }

    /**
     * Retrieve ocr queue record
     *
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function getOcrQueueRecord($id, $with = [])
    {
        return $this->ocrQueue->findWith($id, $with);
    }

    /**
     * Build the ocr and send to the queue.
     *
     * @param $subject
     * @return array
     */
    public function buildOcrQueueData($subject)
    {
        $this->ocrData[$subject->_id] = [
            'crop' => $this->ocrCrop,
            'ocr' => '',
            'status' => 'pending',
            'url' => $subject->accessURI
        ];

        return;
    }

    /**
     * Save ocr queue
     * @param $projectId
     * @param $ocrCsv
     * @param $data
     * @param $count
     * @param $batch
     * @return mixed
     */
    public function saveOcrQueue($projectId, $ocrCsv, $data, $count, $batch)
    {
        $queue = null;
        $queue = $this->ocrQueue->create([
            'project_id' => $projectId,
            'ocr_csv_id' => $ocrCsv,
            'data' => json_encode(['subjects' => $data]),
            'subject_count' => $count,
            'subject_remaining' => $count,
            'batch' => $batch
        ]);

        return $queue;
    }

    /**
     * Check tube for existing jobs
     * @return bool
     */
    public function checkOcrQueueForJobs()
    {
        $pheanstalk = $this->queue->getPheanstalk();
        $stats = (array)$pheanstalk->statsTube($this->ocrTube);

        $keyArray = [
            'current-jobs-urgent' => null,
            'current-jobs-ready' => null,
            'current-jobs-reserved' => null,
            'current-jobs-delayed' => null,
            'current-jobs-buried' => null
        ];

        $filtered = array_intersect_key($stats, $keyArray);

        foreach ($filtered as $stat) {
            if ($stat > 0)
                return true;
        }

        return false;
    }

    /**
     * Push ocr data to queue for processing
     *
     * @param $id
     */
    public function queuePush($id)
    {
        $this->queue->push($this->ocrProcessQueue, ['id' => $id], $this->ocrTube);

        return;
    }

    /**
     * Queue later
     * @param $date
     * @param $data
     */
    public function queueLater($date, $data)
    {
        $this->queue->later($date, $this->ocrProcessQueue, $data, $this->ocrTube);

        return;
    }

    /**
     * Chunk array for processing
     * @return array
     */
    public function getChunkQueueData()
    {
        return !empty($this->ocrData) ? array_chunk($this->ocrData, $this->ocrChunk, true) : [];
    }

    /**
     * Send json data as file.
     *
     * @param $record
     * @return bool
     * @throws \Exception
     */
    public function sendOcrFile($record)
    {
        $options = [
            'multipart' => [
                [
                    'name' => 'response',
                    'contents' => 'http'
                ],
                [
                    'name' => 'file',
                    'contents' => $record->data,
                    'filename' => $record->uuid . '.json',
                ]
            ]];

        $response = $this->client->request('POST', $this->ocrPostUrl, $options);

        if ($response->getStatusCode() != '202') {
            throw new Exception(trans('emails.error_ocr_curl',
                ['id' => $record->id, 'message' => print_r($response->getBody(), true)]));
        }

        return;
    }

    /**
     * Request file from ocr server
     *
     * @param $uuid
     * @return string
     */
    public function requestOcrFile($uuid)
    {
        $response = $this->client->get($this->ocrGetUrl . '/' . $uuid . '.json');

        $file = json_decode($response->getBody()->getContents());

        return $file;
    }

    /**
     * Process returned json file from ocr server. Complete job or queue again for processing.
     *
     * @param $file
     * @return bool
     */
    public function processOcrFile($file)
    {

        if ($file->header->status == "error") {
            $this->updateRecord(['error' => 1]);
            $this->addReportError($this->record->id, trans('emails.error_ocr_header'));
            $this->report->reportSimpleError($this->groupId);
            $this->delete();

            return false;
        }

        return true;
    }

    /**
     * Check if ocr file header exists
     *
     * @param $file
     * @return bool
     */
    public function checkOcrFileHeaderExists($file)
    {
        if (!isset($file->header) || empty($file->header)) {
            return false;
        }

        return true;
    }

    /**
     * Check if ocr file status for progress
     *
     * @param $file
     * @return bool
     */
    public function checkOcrFileInProgress($file)
    {
        if ($file->header->status == "in progress") {
            return false;
        }

        return true;
    }

    /**
     * Check ocr file status for error
     *
     * @param $file
     * @return bool
     */
    public function checkOcrFileError($file)
    {
        if ($file->header->status == "error") {
            return false;
        }

        return true;
    }

    /**
     * Update subject ocr fields based on ocr file results
     *
     * @param $file
     * @return array
     */
    public function updateSubjectsFromOcrFile($file)
    {
        $csv = [];
        foreach ($file->subjects as $id => $data) {
            if ($data->ocr == "error") {
                $csv[] = ['id' => $id, 'message' => implode(" -- ", $data->messages), 'url' => $data->url];
                continue;
            }

            $subject = $this->subject->find($id);
            $subject->ocr = $data->ocr;
            $subject->save();
        }

        return $csv;
    }

    /**
     * Get count of remaining subjects
     * @param $record
     * @return mixed
     */
    public function getSubjectRemainingSum($record)
    {
        $count = (int)$this->ocrQueue->getSubjectRemainingSum($record->id);

        return $count == 0 ? $record->subject_count : $count;
    }

    /**
     * Calculate remaining count
     * @param $record
     * @param $file
     */
    public function calculateSubjectRemaining($record, $file)
    {
        return !$file ? $record->subject_count : ($record->subject_count - $file->header->complete);
    }

    /**
     * Set additional queue time
     * @param $count
     * @return static
     */
    public function setQueueLaterTime($count)
    {
        $minutes = $count == 0 ? 0 : round($count / 15);

        return Carbon::now()->addMinutes($minutes);
    }
}