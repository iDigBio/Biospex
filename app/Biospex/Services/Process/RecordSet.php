<?php namespace Biospex\Services\Process;

use Biospex\Repo\Import\ImportInterface;
use Biospex\Services\Curl\Curl;
use Exception;

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
     * Constructor.
     *
     * @param ImportInterface $import
     */
    public function __construct(ImportInterface $import)
    {
        $this->import = $import;

        $this->queue = \Config::get('config.beanstalkd.import');
        $this->recordsetUrl = \Config::get('config.recordsetUrl');
        $this->importDir = \Config::get('config.subjectImportDir');
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
        if (isset($this->data['url'])) {
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
        $rc = new Curl([$this, "response"]);
        $rc->options = [CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1];
        $rc->get($url);
        $result = $rc->execute();

        return $result;
    }

    /**
     * Process response and handle appropriately.
     *
     * @param $return
     * @param $info
     * @return bool
     */
    public function response($return, $info)
    {
        if (! $this->checkHttpCode($info)) {
            return false;
        }

        $this->response = json_decode($return);

        if ($this->response->complete == true && $this->response->task_status == "SUCCESS") {
            return $this->download();
        }

        $this->pushToQueue();

        return true;
    }

    /**
     * Check the response and release if necessary.
     * iDigBio sometimes returns 500 so release job if needed.
     *
     * @param $info
     * @return bool
     */
    public function checkHttpCode($info)
    {
        if ($info['http_code'] == 200) {
            return true;
        }

        $this->release(10);

        return false;
    }

    /**
     * Download zip file.
     *
     * @return bool
     * @throws Exception
     */
    public function download()
    {
        $fileName = $this->data['id'] . ".zip";
        $filePath = $this->importDir . "/" . $fileName;
        if (! file_put_contents($filePath, file_get_contents($this->response->download_url))) {
            throw new \Exception(trans('emails.error_zip_download'));
        }

        $import = $this->importInsert($fileName);

        unset($this->data);
        $this->data = ['id' => $import->id];

        $this->pushToQueue();

        return true;
    }

    /**
     * Push to queue.
     */
    public function pushToQueue()
    {
        $date = \Carbon::now()->addMinutes(5);
        \Queue::later($date, 'Biospex\Services\Queue\DarwinCoreFileImportQueue', $this->data, $this->queue);

        return;
    }

    /**
     * Check if import directory exists.
     *
     * @throws Exception
     */
    protected function checkDir()
    {
        if (! \File::isDirectory($this->importDir)) {
            if (! \File::makeDirectory($this->importDir, 0775, true)) {
                throw new \Exception(trans('emails.error_create_dir', ['directory' => $this->importDir]));
            }
        }

        if (! \File::isWritable($this->importDir)) {
            if (! chmod($this->importDir, 0775)) {
                throw new \Exception(trans('emails.error_write_dir', ['directory' => $this->importDir]));
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
