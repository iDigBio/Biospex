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

use App\Services\Model\ExpeditionService;
use App\Services\Api\PanoptesApiService;

/**
 * Class ZooniverseCsvService
 *
 * @package App\Services\Csv
 */
class ZooniverseCsvService
{
    /**
     * @var \App\Services\Model\ExpeditionService
     */
    private $expeditionService;

    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private $panoptesApiService;

    /**
     * ZooniverseCsvService constructor.
     *
     * @param \App\Services\Model\ExpeditionService $expeditionService
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     */
    public function __construct(ExpeditionService $expeditionService, PanoptesApiService $panoptesApiService)
    {

        $this->expeditionService = $expeditionService;
        $this->panoptesApiService = $panoptesApiService;
    }

    /**
     * Get expedition for processing.
     *
     * @param int $expeditionId
     * @return mixed
     */
    public function getExpedition(int $expeditionId)
    {
        return $this->expeditionService->getExpeditionForZooniverseProcess($expeditionId);
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
            throw new \Exception(t('Missing required expedition variables for Zooniverse classification create. Expedition %s', $expeditionId));
        }

        $this->sendRequest($expedition->panoptesProject->panoptes_workflow_id, 'POST', ['body' => '{"media":{"content_type":"text/csv"}}']);

    }

    /**
     * Build and send request to check csv file creating or ready.
     *
     * @param int $expeditionId
     * @return mixed|null
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function checkCsvRequest(int $expeditionId)
    {
        $expedition = $this->getExpedition($expeditionId);

        if ($this->panoptesApiService->checkForRequiredVariables($expedition)) {
            throw new \Exception(t('Missing required expedition variables for Zooniverse classification check.'));
        }

        return $this->sendRequest($expedition->panoptesProject->panoptes_workflow_id, 'GET');
    }

    /**
     * Download csv file.
     *
     * @param int $expeditionId
     * @param string $uri
     */
    public function downloadCsv(int $expeditionId, string $uri)
    {
        $this->panoptesApiService->panoptesClassificationsDownload($expeditionId, $uri);
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
    public function sendRequest(int $workflowId, string $method, array $extra = [])
    {
        $this->panoptesApiService->setProvider();
        $this->panoptesApiService->checkAccessToken('panoptes_token');
        $uri = $this->panoptesApiService->getPanoptesResourceUri('workflows', $workflowId, true);
        $request = $this->panoptesApiService->buildAuthorizedRequest($method, $uri, $extra);

        return $this->panoptesApiService->sendAuthorizedRequest($request);
    }
}