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

namespace App\Services\Api;

use App\Facades\CountHelper;
use App\Services\Requests\HttpRequest;
use Cache;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\GenericProvider;
use Storage;

/**
 * Class PanoptesApiService
 *
 * @package App\Services\Api
 */
class PanoptesApiService extends HttpRequest
{
    /**
     * @var
     */
    public $provider;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $apiUri;

    /**
     * @var int
     */
    private $subject_count;

    /**
     * @var int
     */
    private $transcriptions_completed;

    /**
     * @var int
     */
    private $transcriptions_goal;

    /**
     * @var int
     */
    private $local_transcriptions_completed;

    /**
     * @var float
     */
    private $percent_completed;

    /**
     * PanoptesApiService constructor.
     */
    public function __construct()
    {
        $this->apiUri = config('config.panoptes.apiUri');
    }

    /**
     * Set provider for Zooniverse
     *
     * @param bool $auth
     */
    public function setProvider($auth = true)
    {
        $config = ! $auth ? [] : [
            'clientId'       => config('config.panoptes.clientId'),
            'clientSecret'   => config('config.panoptes.clientSecret'),
            'redirectUri'    => config('config.panoptes.redirectUri'),
            'urlAccessToken' => config('config.panoptes.tokenUri'),
            'scope'          => config('config.panoptes.scopes'),
        ];

        $this->setHttpProvider($config);
    }

    /**
     * Get generic provider
     *
     * @return GenericProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Build authorized request.
     *
     * @param string $method
     * @param string $uri
     * @param array $extra
     * @return \Psr\Http\Message\RequestInterface
     */
    public function buildAuthorizedRequest(string $method, string $uri, array $extra = [])
    {
        $options = array_merge([
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/vnd.api+json; version=1',
            ],
        ], $extra);

        return $this->buildAuthenticatedRequest($method, $uri, $options);
    }

    /**
     * Send authorized request.
     *
     * @param $request
     * @return mixed
     * @throws GuzzleException
     */
    public function sendAuthorizedRequest($request)
    {
        $response = $this->provider->getHttpClient()->send($request);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get resource uri: projects, workflows, subjects, classifications, users
     *
     * @param $resource
     * @param $id
     * @param bool $export
     * @return string
     */
    public function getPanoptesResourceUri($resource, $id, $export = false)
    {
        return !$export ?
            $this->apiUri . '/' . $resource . '/' . $id :
            $this->apiUri . '/' . $resource . '/' . $id . '/classifications_export';
    }

    /**
     * Get panoptes project.
     *
     * @param $projectId
     * @return array
     */
    public function getPanoptesProject($projectId)
    {
        return Cache::remember(__METHOD__.$projectId, 120, function () use ($projectId) {
            $this->setProvider();
            $this->checkAccessToken('panoptes_token');
            $uri = $this->getPanoptesResourceUri('projects', $projectId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $result = $this->sendAuthorizedRequest($request);

            return $result['projects'][0];
        });
    }

    /**
     * Get panoptes workflow.
     *
     * @param $workflowId
     * @return mixed
     */
    public function getPanoptesWorkflow($workflowId)
    {
        return Cache::remember(__METHOD__.$workflowId, 120, function () use ($workflowId) {
            $this->setProvider();
            $this->checkAccessToken('panoptes_token');
            $uri = $this->getPanoptesResourceUri('workflows', $workflowId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $result = $this->sendAuthorizedRequest($request);

            return $result['workflows'][0];
        });
    }

    /**
     * Get panoptes subject.
     *
     * @param $subjectId
     * @return null
     */
    public function getPanoptesSubject($subjectId)
    {
        return Cache::remember(__METHOD__.$subjectId, 120, function () use ($subjectId) {
            $this->setProvider();
            $this->checkAccessToken('panoptes_token');
            $uri = $this->getPanoptesResourceUri('subjects', $subjectId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $results = $this->sendAuthorizedRequest($request);

            return isset($results['subjects'][0]) ? $results['subjects'][0] : null;
        });
    }

    /**
     * Get panoptes user.
     *
     * @param $userId
     * @return mixed
     */
    public function getPanoptesUser($userId)
    {
        return Cache::remember(__METHOD__.$userId, 120, function () use ($userId) {
            $this->setProvider();
            $this->checkAccessToken('panoptes_token');
            $uri = $this->getPanoptesResourceUri('users', $userId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $results = $this->sendAuthorizedRequest($request);

            return isset($results['users'][0]) ? $results['users'][0] : null;
        });
    }

    /**
     * Send requests to build panoptes classifications download.
     *
     * @param $expeditions
     * @return array
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function panoptesClassificationCreate($expeditions)
    {
        /**
         * @param array $expeditions
         * @return \Generator
         */
        $requests = function ($expeditions)
        {
            foreach ($expeditions as $expedition)
            {
                if ($this->checkForRequiredVariables($expedition))
                {
                    continue;
                }

                $uri = $this->getPanoptesResourceUri('workflows', $expedition->panoptesProject->panoptes_workflow_id, true);
                $request = $this->buildAuthorizedRequest('POST', $uri, ['body' => '{"media":{"content_type":"text/csv"}}']);

                yield $expedition->id => $request;
            }
        };

        $this->setProvider();
        $this->checkAccessToken('panoptes_token');

        return $this->poolBatchRequest($requests($expeditions));
    }

    /**
     * Download csv panoptes classifications.
     *
     * @param $index
     * @param $source
     * @return array
     */
    public function panoptesClassificationsDownload($index, $source)
    {
        $requests = function () use ($index, $source)
        {
            yield $index => function ($poolOpts) use ($index, $source)
            {
                $reqOpts = [
                    'sink' => Storage::path(config('config.nfn_downloads_classification') . '/' . $index . '.csv')
                ];

                if (is_array($poolOpts) && count($poolOpts) > 0)
                {
                    $reqOpts = array_merge($poolOpts, $reqOpts); // req > pool
                }

                return $this->getHttpClient()->getAsync($source, $reqOpts);
            };
        };

        $this->setProvider(false);

        return $this->poolBatchRequest($requests());
    }

    /**
     * Check panoptes classifications file to see if ready for download.
     *
     * @param $expeditions
     * @return array
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function panoptesClassificationsFile($expeditions)
    {
        $requests = function ($expeditions)
        {
            foreach ($expeditions as $expedition)
            {
                if ($this->checkForRequiredVariables($expedition))
                {
                    continue;
                }

                $uri = $this->getPanoptesResourceUri('workflows', $expedition->panoptesProject->panoptes_workflow_id, true);
                $request = $this->buildAuthorizedRequest('GET', $uri);

                yield $expedition->id => $request;
            }
        };

        $this->setProvider();
        $this->checkAccessToken('panoptes_token');

        return $this->poolBatchRequest($requests($expeditions));
    }

    /**
     * Check needed variables.
     *
     * @param $expedition
     * @return bool
     */
    public function checkForRequiredVariables($expedition)
    {
        return null === $expedition ||
            ! isset($expedition->panoptesProject) ||
            null === $expedition->panoptesProject->panoptes_workflow_id ||
            null === $expedition->panoptesProject->panoptes_project_id;
    }

    /**
     * Calculates totals for transcripts and sets properties.
     *
     * @param $workflow
     * @param $expeditionId
     */
    public function calculateTotals($workflow, $expeditionId)
    {
        $this->subject_count = (int) $workflow['subjects_count'];
        $this->transcriptions_goal = (int) $workflow['subjects_count'] * (int) $workflow['retirement']['options']['count'];
        $this->transcriptions_completed = (int) $workflow['classifications_count'];
        $this->local_transcriptions_completed = CountHelper::expeditionTranscriptionCount($expeditionId);
        $this->percent_completed = $this->percentCompleted();
    }

    /**
     * Calculate percent complete for transcriptsions.
     *
     * @return false|float|int
     */
    private function percentCompleted()
    {
        $value = ($this->local_transcriptions_completed === 0) ? 0 :
            ($this->local_transcriptions_completed / $this->transcriptions_goal) * 100;

        return ($value > 100) ? 100 : round($value, 2);
    }

    /**
     * Return subject count.
     *
     * @return int
     */
    public function getSubjectCount()
    {
        return $this->subject_count;
    }

    /**
     * Return transcriptions completed.
     *
     * @return int
     */
    public function getTranscriptionsCompleted()
    {
        return $this->transcriptions_completed;
    }

    /**
     * Return transcriptions_goal.
     *
     * @return int
     */
    public function getTranscriptionsGoal()
    {
        return $this->transcriptions_goal;
    }

    /**
     * Return local_transcriptions_completed.
     *
     * @return int
     */
    public function getLocalTranscriptionsCompleted()
    {
        return $this->local_transcriptions_completed;
    }

    /**
     * Return percent completed.
     *
     * @return float
     */
    public function getPercentCompleted()
    {
        return $this->percent_completed;
    }

    /**
     * Get image url for subject.
     *
     * @param $subjectId
     * @return mixed|null
     */
    public function getSubjectImageLocation($subjectId)
    {
        $subject = $this->getPanoptesSubject($subjectId);
        $locations = collect($subject['locations'])->filter(function ($location) {
            return ! empty($location['image/jpeg']);
        });

        return $locations->isNotEmpty() ? $locations->first()['image/jpeg'] : null;
    }
}