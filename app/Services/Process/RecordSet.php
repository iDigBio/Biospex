<?php namespace App\Services\Process;

use App\Repositories\Contracts\Import;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;

class RecordSet
{

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
     */
    public function __construct(Import $import)
    {
        $this->import = $import;

        $this->tube = Config::get('config.beanstalkd.import');
        $this->recordsetUrl = Config::get('config.recordset_url');
        $this->importDir = Config::get('config.subject_import_dir');
    }

    /**
     * Process.
     *
     * @param $data
     * @return bool|string
     * @throws Exception
     */
    public function process($data)
    {
        $this->checkDir();

        $this->data = $data;

        $url = $this->setUrl();

        return $this->send($url);
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
     * @return bool|string
     */
    public function send($url)
    {
        $client = new Client();
        $response = $client->get($url, [
            'headers' => ['Accept' => 'application/json']
        ]);

        if ($response->getStatusCode() != 200)
        {
            return;
        }

        $this->response = json_decode($response->getBody()->getContents());

        if ($this->response->complete == true && $this->response->task_status == "SUCCESS")
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
     * @return bool
     * @throws Exception
     */
    public function download()
    {
        $fileName = $this->data['id'] . '.zip';
        $filePath = $this->importDir . '/' . $fileName;
        if (!file_put_contents($filePath, file_get_contents($this->response->download_url)))
        {
            throw new \Exception(trans('emails.error_zip_download'));
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
     * Check if import directory exists.
     *
     * @throws Exception
     */
    protected function checkDir()
    {
        if (!File::isDirectory($this->importDir))
        {
            if (!File::makeDirectory($this->importDir, 0775, true))
            {
                throw new Exception(trans('emails.error_create_dir', ['directory' => $this->importDir]));
            }
        }

        if (!File::isWritable($this->importDir))
        {
            if (!chmod($this->importDir, 0775))
            {
                throw new Exception(trans('emails.error_write_dir', ['directory' => $this->importDir]));
            }
        }

        return;
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
