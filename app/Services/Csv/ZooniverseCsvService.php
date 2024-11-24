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

namespace App\Services\Csv;

use App\Services\Api\AwsS3ApiService;
use App\Services\Api\PanoptesApiService;
use App\Services\Expedition\ExpeditionService;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/**
 * Class ZooniverseCsvService
 */
class ZooniverseCsvService
{
    /**
     * ZooniverseCsvService constructor.
     */
    public function __construct(
        protected ExpeditionService $expeditionService,
        protected PanoptesApiService $panoptesApiService,
        protected AwsS3ApiService $awsS3ApiService
    ) {}

    /**
     * Get expedition for processing.
     */
    public function getExpedition(int $expeditionId): mixed
    {
        return $this->expeditionService->getExpeditionForZooniverseProcess($expeditionId);
    }

    /**
     * Create and send request for csv creation.
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function createCsvRequest(int $expeditionId)
    {
        $expedition = $this->getExpedition($expeditionId);

        if ($this->panoptesApiService->checkForRequiredVariables($expedition)) {
            throw new \Exception(t('Missing required expedition variables for Zooniverse classification create. Expedition %s', $expeditionId));
        }

        $this->sendWorkflowRequest($expedition->panoptesProject->panoptes_workflow_id, 'POST', ['body' => '{"media":{"content_type":"text/csv"}}']);

    }

    /**
     * Build and send request to check csv file creating or ready.
     *
     * @throws \Exception
     */
    public function checkCsvRequest(int $expeditionId): mixed
    {
        $expedition = $this->getExpedition($expeditionId);

        if ($this->panoptesApiService->checkForRequiredVariables($expedition)) {
            throw new \Exception(t('Missing required expedition variables for Zooniverse classification check for Expedition ID %s.', $expeditionId));
        }

        try {
            return $this->sendWorkflowRequest($expedition->panoptesProject->panoptes_workflow_id, 'GET');
        } catch (GuzzleException|IdentityProviderException $e) {
            return false;
        }
    }

    /**
     * Download csv file to S3 lambda-reconciliation and trigger reconciliation.
     *
     * @see \App\Listeners\LabelReconciliationListener for returned data.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function downloadCsv(int $expeditionId, string $uri): void
    {
        $filePath = config('zooniverse.directory.lambda-reconciliation').'/'.$expeditionId.'.csv';
        $stream = $this->awsS3ApiService->createS3BucketStream(config('filesystems.disks.s3.bucket'), $filePath, 'w', false);
        $opts = [
            'sink' => $stream,
        ];
        $this->panoptesApiService->setHttpProvider();
        $this->panoptesApiService->getHttpClient()->request('GET', $uri, $opts);
    }

    /**
     * Send request.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function sendWorkflowRequest(int $workflowId, string $method, array $extra = []): mixed
    {
        $this->panoptesApiService->setHttpProvider($this->panoptesApiService->getConfig());
        $this->panoptesApiService->checkAccessToken('panoptes_token');
        $uri = $this->panoptesApiService->getPanoptesResourceUri('workflows', $workflowId, true);
        $request = $this->panoptesApiService->buildAuthorizedRequest($method, $uri, $extra);

        return $this->panoptesApiService->sendAuthorizedRequest($request);
    }

    /**
     * Calculate time difference.
     * If errors, csv doesn't exist yet.
     * Hours must be greater than 24 hours for Zooniverse to create CSV.
     */
    public function checkDateTime(array $result): bool
    {
        return empty($result['media'][0]['updated_at']) ?
            $this->parseTime($result['media'][0]['created_at']) > 32 :
            $this->parseTime($result['media'][0]['updated_at']) > 32;
    }

    /**
     * Parse time.
     */
    public function parseTime(string $date): int
    {
        $date = Carbon::parse($date);
        $now = Carbon::now('UTC');

        return $date->diffInHours($now);
    }
}
