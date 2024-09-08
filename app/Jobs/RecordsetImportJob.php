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

use App\Notifications\Generic;
use App\Repositories\ImportRepository;
use App\Repositories\ProjectRepository;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Storage;

/**
 * Class RecordsetImportJob
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

    public $data;

    /**
     * @var \App\Repositories\ImportRepository
     */
    public $importRepo;

    /**
     * Curl response
     */
    public $response;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->onQueue(config('config.queue.import'));
    }

    /**
     * Execute the job.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(ImportRepository $importRepo, ProjectRepository $projectRepo): void
    {
        $this->importRepo = $importRepo;
        $project = $projectRepo->getProjectForDarwinImportJob($this->data['id']);
        $users = $project->group->users->push($project->group->owner);

        try {
            $url = $this->setUrl();
            $this->send($url);
        } catch (Exception $e) {

            $attributes = [
                'subject' => 'DWC Record Set Import Error',
                'html' => [
                    t('An error occurred while importing the Darwin Core Archive using a record set.'),
                    t('Project: %s', $project->title),
                    t('ID: %s'.$project->id),
                    t('File: %s', $e->getFile()),
                    t('Line: %s', $e->getLine()),
                    t('Message: %s', $e->getMessage()),
                    t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
                ],
            ];
            Notification::send($users, new Generic($attributes, true));
        }
    }

    /**
     * Set url for request.
     */
    private function setUrl(): string
    {
        if (isset($this->data['url'])) {
            return $this->data['url'];
        }

        return str_replace('RECORDSET_ID', $this->data['id'], config('config.recordset_url'));
    }

    /**
     * Send request to url.
     *
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function send($url): void
    {
        $client = new Client;
        $response = $client->get($url, ['headers' => ['Accept' => 'application/json']]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception(t('Http call to :url returned status code :code', [':url' => $url,
                ':code' => $response->getStatusCode(),
            ]));
        }

        $this->response = json_decode($response->getBody()->getContents());

        if ($this->response->complete === true && $this->response->task_status === 'SUCCESS') {
            $import = $this->download();
            DwcFileImportJob::dispatch($import)->delay(now()->addMinutes(5));

            return;
        }

        RecordsetImportJob::dispatch($this->data)->delay(now()->addMinutes(5));
    }

    /**
     * Download zip file.
     *
     * @throws \Exception
     */
    public function download(): mixed
    {
        $fileName = $this->data['id'].'.zip';
        $file = file_get_contents($this->response->download_url);
        $filePath = config('config.import_dir').'/'.$fileName;

        if (Storage::disk('efs')->put($filePath, $file) === false) {
            throw new Exception(t('Unable to complete zip download for Darwin Core Archive.'));
        }

        return $this->importRepo->create([
            'user_id' => $this->data['user_id'],
            'project_id' => $this->data['project_id'],
            'file' => $filePath,
        ]);
    }
}
