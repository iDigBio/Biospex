<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Models\Import;
use App\Notifications\Generic;
use App\Services\Project\ProjectService;
use App\Services\Requests\HttpRequest;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class RecordsetImportJob
 *
 * Handles the asynchronous import of recordsets from external APIs. This job:
 * - Fetches recordset data from configured URLs
 * - Processes API responses and handles JSON data
 * - Downloads and stores Darwin Core Archive (DwC-A) files
 * - Manages retries and error handling for HTTP requests
 * - Queues subsequent DwcBatchImportJob for processing downloaded files
 *
 * @property Import $import Import model instance for tracking import status and metadata
 * @property object $response API response object containing recordset data and download URLs
 */
class RecordsetImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 1800;

    /**
     * Import model instance for tracking import status and metadata.
     */
    public Import $import;

    /**
     * API response object containing recordset data and download URLs.
     */
    public object $response;

    /**
     * Maximum number of retry attempts for HTTP requests.
     */
    private int $maxRetries = 3;

    /**
     * HTTP request timeout in seconds.
     */
    private int $requestTimeout = 60;

    /**
     * HTTP request service instance.
     */
    private HttpRequest $httpRequest;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $data)
    {
        $this->onQueue(config('config.queue.import'));
    }

    /**
     * Execute the job.
     */
    public function handle(Import $import, ProjectService $projectService, HttpRequest $httpRequest): void
    {
        $this->import = $import;
        $this->httpRequest = $httpRequest;

        try {
            $url = $this->setUrl();
            $this->send($url);
        } catch (Throwable $throwable) {

            $project = $projectService->getProjectForDarwinImportJob($this->data['project_id']);
            $users = $project->group->users->push($project->group->owner);

            $attributes = [
                'subject' => 'DWC Record Set Import Error',
                'html' => [
                    t('An error occurred while importing the Darwin Core Archive using a record set.'),
                    t('Project: %s', $project->title),
                    t('ID: %s', $project->id),
                    t('File: %s', $throwable->getFile()),
                    t('Line: %s', $throwable->getLine()),
                    t('Message: %s', $throwable->getMessage()),
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
        // Check if the ID itself contains "http" and treat it as a URL
        if (str_contains($this->data['id'], 'http')) {
            return $this->data['id'];
        }

        return str_replace('RECORDSET_ID', $this->data['id'], config('config.recordset_url'));
    }

    /**
     * Create a properly configured Guzzle HTTP client with retry middleware.
     */
    private function createHttpClient(): Client
    {
        return $this->httpRequest
            ->setMaxRetries($this->maxRetries)
            ->createDirectHttpClient([
                'timeout' => $this->requestTimeout,
                'connect_timeout' => 10,
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'Biospex/1.0',
                ],
            ]);
    }

    /**
     * Send request to url.
     *
     * @throws \Exception
     */
    public function send(string $url): void
    {
        try {
            $client = $this->createHttpClient();
            $response = $client->get($url);

            if ($response->getStatusCode() !== 200) {
                throw new Exception(t('Http call to :url returned status code :code', [
                    ':url' => $url,
                    ':code' => $response->getStatusCode(),
                ]));
            }

            $responseContent = $response->getBody()->getContents();
            $this->response = json_decode($responseContent);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(t('Invalid JSON response from :url: :error', [
                    ':url' => $url,
                    ':error' => json_last_error_msg(),
                ]));
            }

            if (($this->response->complete ?? false) === true && ($this->response->task_status ?? '') === 'SUCCESS') {
                $import = $this->download();
                DwcBatchImportJob::dispatch($import)->delay(now()->addMinutes(5));

                return;
            }

            // Re-queue the job to check status again in 5 minutes
            RecordsetImportJob::dispatch($this->data)->delay(now()->addMinutes(5));

        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $message = t('HTTP request failed for :url with status :code: :message', [
                ':url' => $url,
                ':code' => $statusCode,
                ':message' => $e->getMessage(),
            ]);

            Log::error('RecordsetImportJob: Request exception', [
                'url' => $url,
                'status_code' => $statusCode,
                'error' => $e->getMessage(),
            ]);

            throw new Exception($message, 0, $e);
        } catch (GuzzleException $e) {
            $message = t('Guzzle HTTP error for :url: :message', [
                ':url' => $url,
                ':message' => $e->getMessage(),
            ]);

            Log::error('RecordsetImportJob: Guzzle exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw new Exception($message, 0, $e);
        }
    }

    /**
     * Download zip file.
     *
     * @throws \Exception
     */
    public function download(): mixed
    {
        if (! isset($this->response->download_url)) {
            throw new Exception(t('Download URL not found in response.'));
        }

        $fileName = $this->data['id'].'.zip';
        $downloadUrl = $this->response->download_url;
        $filePath = config('config.import_dir').'/'.$fileName;

        try {
            $client = $this->createHttpClient();
            $response = $client->get($downloadUrl);

            if ($response->getStatusCode() !== 200) {
                throw new Exception(t('Failed to download file from :url, status code: :code', [
                    ':url' => $downloadUrl,
                    ':code' => $response->getStatusCode(),
                ]));
            }

            $fileContent = $response->getBody()->getContents();

            if (empty($fileContent)) {
                throw new Exception(t('Downloaded file is empty from :url', [':url' => $downloadUrl]));
            }

            if (Storage::disk('efs')->put($filePath, $fileContent) === false) {
                throw new Exception(t('Unable to save downloaded file to :path', [':path' => $filePath]));
            }

            return $this->import->create([
                'user_id' => $this->data['user_id'],
                'project_id' => $this->data['project_id'],
                'file' => $filePath,
            ]);

        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $message = t('HTTP request failed during download from :url with status :code: :message', [
                ':url' => $downloadUrl,
                ':code' => $statusCode,
                ':message' => $e->getMessage(),
            ]);

            Log::error('RecordsetImportJob: Download request exception', [
                'download_url' => $downloadUrl,
                'status_code' => $statusCode,
                'error' => $e->getMessage(),
            ]);

            throw new Exception($message, 0, $e);
        } catch (GuzzleException $e) {
            $message = t('Guzzle HTTP error during download from :url: :message', [
                ':url' => $downloadUrl,
                ':message' => $e->getMessage(),
            ]);

            Log::error('RecordsetImportJob: Download Guzzle exception', [
                'download_url' => $downloadUrl,
                'error' => $e->getMessage(),
            ]);

            throw new Exception($message, 0, $e);
        }
    }
}
