<?php

namespace App\Services\Requests;

use Illuminate\Support\Facades\Artisan;
use GuzzleHttp\Client;

class OcrRequest extends HttpRequest
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Ocr constructor.
     */
    public function __construct()
    {
        $this->setHttpProvider();
        $this->client = $this->getHttpClient();
    }

    /**
     * Send json data as file.
     *
     * @param $record
     * @throws \Exception
     */
    public function sendOcrFile($record)
    {
        $options = [
            'multipart' => [
                [
                    'name'     => 'response',
                    'contents' => 'http'
                ],
                [
                    'name'     => 'file',
                    'contents' => $record->data,
                    'filename' => $record->uuid.'.json',
                ],
            ],
        ];

        $response = $this->client->request('POST', config('config.ocr_post_url'), $options);

        if ($response->getStatusCode() !== 202) {
            throw new \Exception(trans('errors.ocr_send_error', [
                'title'   => $record->project->title,
                'id'      => $record->project->id,
                'message' => print_r($response->getBody(), true),
            ]));
        }
    }

    /**
     * Request file from ocr server
     *
     * @param $uuid
     * @return mixed
     */
    public function requestOcrFile($uuid)
    {
        $response = $this->client->get(config('config.ocr_get_url').'/'.$uuid.'.json');
        $contents = $response->getBody()->getContents();

        return json_decode($contents);
    }

    /**
     * Check if ocr file header exists
     *
     * @param $file
     * @return bool
     */
    public function checkOcrFileHeaderExists($file)
    {
        return isset($file->header);
    }

    /**
     * Check ocr file status for progress
     *
     * @param $file
     * @return bool
     */
    public function checkOcrFileInProgress($file)
    {
        return $file->header->status === 'in progress';
    }

    /**
     * Check ocr file status for error
     *
     * @param $file
     * @return bool
     */
    public function checkOcrFileError($file)
    {
        return $file->header->status === 'error';
    }

    /**
     * Send request to delete json files on ocr server.
     *
     * @param $files
     */
    public function deleteJsonFiles($files)
    {
        Artisan::call('ocrfile:delete', ['files' => $files]);
    }
}