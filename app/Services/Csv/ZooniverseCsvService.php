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

use App\Repositories\ExpeditionRepository;
use App\Services\Api\PanoptesApiService;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Storage;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/**
 * Class ZooniverseCsvService
 *
 * @package App\Services\Csv
 */
class ZooniverseCsvService
{
    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepo;

    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private PanoptesApiService $panoptesApiService;

    /**
     * ZooniverseCsvService constructor.
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     */
    public function __construct(ExpeditionRepository $expeditionRepo, PanoptesApiService $panoptesApiService)
    {

        $this->expeditionRepo = $expeditionRepo;
        $this->panoptesApiService = $panoptesApiService;
    }

    /**
     * Get expedition for processing.
     *
     * @param int $expeditionId
     * @return mixed
     */
    public function getExpedition(int $expeditionId): mixed
    {
        return $this->expeditionRepo->getExpeditionForZooniverseProcess($expeditionId);
    }

    /**
     * Create and send request for csv creation.
     *
     * @param int $expeditionId
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function createCsvRequest(int $expeditionId)
    {
        $expedition = $this->getExpedition($expeditionId);

        if ($this->panoptesApiService->checkForRequiredVariables($expedition)) {
            throw new \Exception(t('Missing required expedition variables for NfnPanoptes classification create. Expedition %s', $expeditionId));
        }

        $this->sendWorkflowRequest($expedition->panoptesProject->panoptes_workflow_id, 'POST', ['body' => '{"media":{"content_type":"text/csv"}}']);

    }

    /**
     * Build and send request to check csv file creating or ready.
     *
     * @param int $expeditionId
     * @return mixed
     * @throws \Exception
     */
    public function checkCsvRequest(int $expeditionId): mixed
    {
        $expedition = $this->getExpedition($expeditionId);

        if ($this->panoptesApiService->checkForRequiredVariables($expedition)) {
            throw new \Exception(t('Missing required expedition variables for NfnPanoptes classification check for Expedition ID %s.', $expeditionId));
        }

        try {
            return $this->sendWorkflowRequest($expedition->panoptesProject->panoptes_workflow_id, 'GET');
        } catch (GuzzleException | IdentityProviderException $e) {
            return false;
        }
    }

    /**
     * Download csv file.
     *
     * @param int $expeditionId
     * @param string $uri
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function downloadCsv(int $expeditionId, string $uri)
    {
        $opts = [
            'sink' => Storage::path(config('config.aws_s3_nfn_downloads.classification') . '/' . $expeditionId . '.csv')
        ];

        $this->panoptesApiService->setHttpProvider();
        $this->panoptesApiService->getHttpClient()->request('GET', $uri, $opts);
    }

    /**
     * Send request.
     *
     * @param int $workflowId
     * @param string $method
     * @param array $extra
     * @return mixed
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
     * Check for errors field on zooniverse classification.
     *
     * @param array $result
     * @return bool
     */
    public function checkErrors(array $result): bool
    {
        return isset($result['errors']);
    }

    /**
     * Calculate time difference.
     * If errors, csv doesn't exist yet.
     * Hours must be greater than 24 hours for NfnPanoptes to create CSV.
     *
     * @param array $result
     * @return bool
     */
    public function checkDateTime(array $result): bool
    {
        return empty($result['media'][0]['updated_at']) ?
            $this->parseTime($result['media'][0]['created_at']) > 32 :
            $this->parseTime($result['media'][0]['updated_at']) > 32;
    }

    /**
     * Parse time.
     *
     * @param string $date
     * @return int
     */
    public function parseTime(string $date): int
    {
        $date = Carbon::parse($date);
        $now = Carbon::now('UTC');

        return $date->diffInHours($now);
    }
}