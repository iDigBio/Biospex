<?php

namespace App\Jobs;

use App\Repositories\Interfaces\Import;
use App\Repositories\Interfaces\Project;
use App\Notifications\DarwinCoreImportError;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RecordsetImportJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var
     */
    public $data;

    /**
     * @var Import
     */
    public $importContract;

    /**
     * Curl response
     * @var
     */
    public $response;

    /**
     * Create a new job instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->onQueue(config('config.beanstalkd.import'));
    }

    /**
     * Execute the job.
     *
     * @param Import $importContract
     * @param Project $projectContract
     */
    public function handle(
        Import $importContract,
        Project $projectContract
    )
    {
        $this->importContract = $importContract;

        try
        {
            $url = $this->setUrl();
            $this->send($url);
        }
        catch (\Exception $e)
        {
            $project = $projectContract->findWith($this->data['project_id'], ['group.owner']);

            $message = trans('errors.import_process', [
                'title'   => $project->title,
                'id'      => $project->id,
                'message' => $e->getMessage()
            ]);

            $project->group->owner->notify(new DarwinCoreImportError($message));
        }
    }

    /**
     * Set url - recordset or download. iDigBio responds with status url if recordset already set.
     *
     * @return mixed
     */
    private function setUrl()
    {
        if (isset($this->data['url']))
        {
            return $this->data['url'];
        }

        $this->data['url'] = str_replace('RECORDSET_ID', $this->data['id'], config('config.recordset_url'));

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
            $import = $this->download();
            DwcFileImportJob::dispatch($import)->delay(now()->addMinutes(5));

            return;
        }

        RecordsetImportJob::dispatch($this->data)->delay(now()->addMinutes(5));
    }


    /**
     * Download zip file.
     *
     * @return mixed
     * @throws \Exception
     */
    public function download()
    {
        $fileName = $this->data['id'] . '.zip';
        $filePath = config('config.subject_import_dir') . '/' . $fileName;
        if ( ! file_put_contents($filePath, file_get_contents($this->response->download_url)))
        {
            throw new \Exception(trans('errors.zip_download'));
        }

        $import = $this->importContract->create([
            'user_id'    => $this->data['user_id'],
            'project_id' => $this->data['project_id'],
            'file'       => $fileName
        ]);

        return $import;
    }
}
