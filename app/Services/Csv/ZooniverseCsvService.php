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

use App\Repositories\Interfaces\Expedition;
use App\Services\Api\PanoptesApiService;

class ZooniverseCsvService
{
    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private $panoptesApiService;

    /**
     * ZooniverseCsvService constructor.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     */
    public function __construct(Expedition $expeditionContract, PanoptesApiService $panoptesApiService)
    {

        $this->expeditionContract = $expeditionContract;
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
        return $this->expeditionContract->getExpeditionForZooniverseProcess($expeditionId);
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
            throw new \Exception(t('Missing required expedition variables for Zooniverse classification create.'));
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

        $result = $this->sendRequest($expedition->panoptesProject->panoptes_workflow_id, 'GET');

        if ($result['media'][0]['metadata']['state'] === 'creating') {
            return null;
        }

        if ($result['media'][0]['metadata']['state'] === 'ready') {
            return $result['media'][0]['src'];
        }

        throw new \Exception(t('Result state for checking classification completion is invalid for Expedition ID %s', $expeditionId));
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