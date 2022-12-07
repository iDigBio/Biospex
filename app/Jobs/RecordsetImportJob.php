<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Notifications\DarwinCoreImportError;
use App\Repositories\ImportRepository;
use App\Repositories\ProjectRepository;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;

/**
 * Class RecordsetImportJob
 *
 * @package App\Jobs
 */
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
     * @var \App\Repositories\ImportRepository
     */
    public $importRepo;

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
        $this->onQueue(config('config.queues.import'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\ImportRepository $importRepo
     * @param \App\Repositories\ProjectRepository $projectRepo
     */
    public function handle(
        ImportRepository $importRepo,
        ProjectRepository $projectRepo
    )
    {
        $this->importRepo = $importRepo;

        try
        {
            $url = $this->setUrl();
            $this->send($url);
        }
        catch (Exception $e)
        {
            $project = $projectRepo->findWith($this->data['project_id'], ['group.owner']);

            $message = [
                'File: ' . $e->getFile(),
                'Line: ' . $e->getLine(),
                'Message: ' . $e->getMessage()
            ];

            $project->group->owner->notify(new DarwinCoreImportError($project->title, $project->id, $message));
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
            throw new Exception(t('Http call to :url returned status code :code', [':url' => $url, ':code' => $response->getStatusCode()]));
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
    public function download(): mixed
    {
        $fileName = $this->data['id'] . '.zip';
        $file = file_get_contents($this->response->download_url);
        $filePath = config('config.import_dir') . '/' . $fileName;

        if (Storage::disk('efs')->put($filePath, $file) === false)
        {
            throw new Exception(t('Unable to complete zip download for Darwin Core Archive.'));
        }

        return $this->importRepo->create([
            'user_id'    => $this->data['user_id'],
            'project_id' => $this->data['project_id'],
            'file'       => $filePath
        ]);
    }
}
