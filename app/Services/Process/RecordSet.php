<?php 

namespace App\Services\Process;

use App\Interfaces\Import;
use App\Services\File\FileService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Queue;

class RecordSet
{

    /**
     * @var Import
     */
    protected $importContract;

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
     * @param Import $importContract
     * @param FileService $fileService
     */
    public function __construct(Import $importContract, FileService $fileService)
    {
        $this->importContract = $importContract;
        $this->fileService = $fileService;

        $this->tube = config('config.beanstalkd.import');
        $this->recordsetUrl = config('config.recordset_url');
        $this->importDir = config('config.subject_import_dir');
    }

    /**
     * Process.
     *
     * @param $data
     * @throws \Exception
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
     * @throws \Exception
     */
    public function send($url)
    {
        $client = new Client();
        $response = $client->get($url, ['headers' => ['Accept' => 'application/json']]);

        if ($response->getStatusCode() !== 200)
        {
            throw new \Exception(trans('errors.http_status_code', ['url' => $url, 'code' => $response->getStatusCode()]));
        }

        $this->response = json_decode($response->getBody()->getContents());

        if ($this->response->complete === true && $this->response->task_status === 'SUCCESS')
        {
            $this->download();
            $this->pushToQueue();

            return;
        }

        $this->pushToQueue(true);
    }


    /**
     * Download zip file.
     *
     * @throws \Exception
     */
    public function download()
    {
        $fileName = $this->data['id'] . '.zip';
        $filePath = $this->importDir . '/' . $fileName;
        if ( ! file_put_contents($filePath, file_get_contents($this->response->download_url)))
        {
            throw new \Exception(trans('errors.zip_download'));
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
        $import = $this->importContract->create([
            'user_id'    => $this->data['user_id'],
            'project_id' => $this->data['project_id'],
            'file'       => $filename
        ]);

        return $import;
    }
}
