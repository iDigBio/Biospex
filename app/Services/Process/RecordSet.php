<?php namespace App\Services\Process;

use App\Exceptions\BiospexException;
use App\Exceptions\DownloadFileException;
use App\Exceptions\RequestException;
use App\Repositories\Contracts\Import;
use App\Services\File\FileService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Queue;

class RecordSet
{

    /**
     * @var Import
     */
    protected $import;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * Data from job queue.
     *
     * @var array
     */
    public $data;

    /**
     * Curl response.
     *
     * @var array
     */
    public $response;

    /**
     * @var
     */
    public $tube;

    /**
     * Constructor.
     *
     * @param Import $import
     * @param FileService $fileService
     */
    public function __construct(Import $import, FileService $fileService)
    {
        $this->import = $import;
        $this->fileService = $fileService;

        $this->tube = Config::get('config.beanstalkd.import');
        $this->recordsetUrl = Config::get('config.recordset_url');
        $this->importDir = Config::get('config.subject_import_dir');
    }

    /**
     * Process.
     *
     * @param $data
     * @throws BiospexException
     */
    public function process($data)
    {
        $this->fileService->makeDirectory($this->importDir);

        $this->data = $data;

        $url = $this->setUrl();

        $this->send($url);
    }

    /**
     * Set url - recordset or download. iDigBio responds with status url if recordset already set.
     *
     * @return mixed
     */
    public function setUrl()
    {
        if (isset($this->data['url']))
        {
            return $this->data['url'];
        }

        $this->data['url'] = str_replace('RECORDSET_ID', $this->data['id'], $this->recordsetUrl);

        return $this->data['url'];
    }

    /**
     * Send request to url.
     *
     * @param $url
     * @throws BiospexException
     */
    public function send($url)
    {
        $client = new Client();
        $response = $client->get($url, ['headers' => ['Accept' => 'application/json']]);

        if ($response->getStatusCode() !== 200)
        {
            throw new RequestException(trans('errors.http_status_code', ['url' => $url, 'code' => $response->getStatusCode()]));
        }

        try {
            $this->response = json_decode($response->getBody()->getContents());

            if ($this->response->complete === true && $this->response->task_status === 'SUCCESS')
            {
                $this->download();
                $this->pushToQueue();

                return;
            }

            $this->pushToQueue(true);
        }
        catch(\RuntimeException $e)
        {
            throw new RequestException($e->getMessage());
        }
    }


    /**
     * Download zip file.
     *
     * @throws DownloadFileException
     */
    public function download()
    {
        $fileName = $this->data['id'] . '.zip';
        $filePath = $this->importDir . '/' . $fileName;
        if ( ! file_put_contents($filePath, file_get_contents($this->response->download_url)))
        {
            throw new DownloadFileException(trans('errors.zip_download'));
        }

        $import = $this->importInsert($fileName);

        unset($this->data);
        $this->data = ['id' => $import->id];
    }

    /**
     * Push to queue.
     * @param bool $requeue
     */
    public function pushToQueue($requeue = false)
    {
        $class = ($requeue) ? 'App\Services\Queue\RecordSetImportQueue' : 'App\Services\Queue\DarwinCoreFileImportQueue';
        $date = Carbon::now()->addMinutes(5);
        Queue::later($date, $class, $this->data, $this->tube);
    }

    /**
     * Insert record into import table.
     *
     * @param $filename
     * @return mixed
     */
    protected function importInsert($filename)
    {
        $import = $this->import->create([
            'user_id'    => $this->data['user_id'],
            'project_id' => $this->data['project_id'],
            'file'       => $filename
        ]);

        return $import;
    }
}
