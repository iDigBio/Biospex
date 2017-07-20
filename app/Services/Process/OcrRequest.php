<?php

namespace App\Services\Process;

use App\Exceptions\RequestException;
use App\Repositories\Contracts\SubjectContract;
use Illuminate\Support\Facades\Artisan;
use GuzzleHttp\Client;

class OcrRequest
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var SubjectContract
     */
    protected $subjectContract;
    
    /**
     * Ocr constructor.
     *
     * @param SubjectContract $subjectContract
     */
    public function __Construct(
        SubjectContract $subjectContract
    )
    {
        $this->client = new Client();
        $this->subjectContract = $subjectContract;
    }

    /**
     * Send json data as file.
     *
     * @param $record
     * @return bool
     * @throws RequestException
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
                    'filename' => $record->uuid . '.json',
                ]
            ]];

        $response = $this->client->request('POST', config('config.ocr_post_url'), $options);

        if ($response->getStatusCode() !== 202) {
            throw new RequestException(trans('errors.ocr_send_error',
                ['title' => $record->title, 'id' => $record->id, 'message' => print_r($response->getBody(), true)]));
        }
    }

    /**
     * Request file from ocr server
     *
     * @param $uuid
     * @return mixed|void
     * @throws RequestException
     */
    public function requestOcrFile($uuid)
    {
        try
        {
            $response = $this->client->get(config('config.ocr_get_url') . '/' . $uuid . '.json');
            $contents = $response->getBody()->getContents();

            return json_decode($contents);
        }
        catch (\RuntimeException $e)
        {
            return '';
        }
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
     * Update subject ocr fields based on ocr file results
     *
     * @param $file
     * @return array
     */
    public function updateSubjectsFromOcrFile($file)
    {
        $csv = [];
        foreach ($file->subjects as $id => $data)
        {
            if ($data->ocr === 'error')
            {
                $csv[] = ['id' => $id, 'message' => implode(' -- ', $data->messages), 'url' => $data->url];
                continue;
            }

            $subject = $this->subjectContract->setCacheLifetime(0)->find($id);
            if ($subject === null)
            {
                $csv[] = ['id' => $id, 'message' => 'Could not locate associated subject in database', 'url' => ''];
                continue;
            }

            $subject->ocr = $data->ocr;
            $this->subjectContract->update($subject->_id, $subject->toArray());
        }

        return $csv;
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