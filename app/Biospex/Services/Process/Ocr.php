<?php

namespace Biospex\Services\Process;

use Queue;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostFile;
use Illuminate\Config\Repository as Config;
use Biospex\Repo\OcrQueue\OcrQueueInterface;
use Biospex\Repo\Subject\SubjectInterface;

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
     * @var OcrQueueInterface
     */
    protected $queue;

    /**
     * @var SubjectInterface
     */
    protected $subject;

    /**
     * @var mixed
     */
    public $ocrCrop;

    /**
     * @var mixed
     */
    public $disableOcr;

    /**
     * @var mixed
     */
    public $ocrQueue;

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
     * Construct
     *
     * @param Client $client
     * @param Config $config
     * @param OcrQueueInterface $queue
     * @param SubjectInterface $subject
     */
    public function __Construct(
        Config $config,
        OcrQueueInterface $queue,
        SubjectInterface $subject
    ) {
        $this->client = new Client();
        $this->config = $config;
        $this->queue = $queue;
        $this->subject = $subject;

        $this->ocrCrop = $config->get('config.ocrCrop');
        $this->disableOcr = $config->get('config.disableOcr');
        $this->ocrQueue = $config->get('config.beanstalkd.ocr');
        $this->ocrPostUrl = $config->get('config.ocrPostUrl');
        $this->ocrGetUrl = $config->get('config.ocrGetUrl');
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
        return $this->queue->findWith($id, $with);
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
            'crop'   => $this->ocrCrop,
            'ocr'    => '',
            'status' => 'pending',
            'url'    => $subject->accessURI
        ];

        return;
    }

    /**
     * Save OCR data for later processing.
     *
     * @param $projectId
     * @param $data
     * @param $count
     * @return mixed
     */
    public function saveOcrQueue($projectId, $data, $count)
    {
        $queue = $this->queue->create([
            'project_id'    => $projectId,
            'data'          => json_encode(['subjects' => $data]),
            'subject_count' => $count
        ]);

        return $queue->id;
    }

    /**
     * Push ocr data to queue for processing
     *
     * @param $id
     */
    public function pushToQueue($id)
    {
        Queue::push('Biospex\Services\Queue\OcrProcessQueue', ['id' => $id],
            $this->ocrQueue);

        return;
    }

    /**
     * Retrieve ocr data array.
     *
     * @return mixed
     */
    public function getOcrQueueData()
    {
        return $this->ocrData;
    }

    /**
     * Retrieve count for ocr data array.
     *
     * @return int
     */
    public function getOcrQueueDataCount()
    {
        return count($this->ocrData);
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
            'body'    => ['response' => 'http'],
            'headers' => ['Content-Type' => 'multipart/form-data']
        ];
        $request = $this->client->createRequest('POST', $this->ocrPostUrl, $options);
        $postBody = $request->getBody();
        $postBody->addFile(new PostFile('file', $record->data, $record->uuid . '.json'));
        $response = $this->client->send($request);

        if ($response->getStatusCode() != '202') {
            throw new \Exception(trans('emails.error_ocr_curl',
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

        return json_decode($response->getBody()->getContents());
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
        if (empty($file->header)) {
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
}