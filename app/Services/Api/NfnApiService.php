<?php

namespace App\Services\Api;

use Cache;

class NfnApiService extends NfnApi
{
    /**
     * Get panoptes project.
     *
     * @param $projectId
     * @return array
     */
    public function getPanoptesProject($projectId)
    {
        $project = Cache::remember(__METHOD__.$projectId, 120, function () use ($projectId) {
            $this->setProvider();
            $this->checkAccessToken('panoptes_token');
            $uri = $this->getProjectUri($projectId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $result = $this->sendAuthorizedRequest($request);

            return $result['projects'][0];
        });

        return $project;
    }

    /**
     * Get panoptes workflow.
     *
     * @param $workflowId
     * @return mixed
     */
    public function getPanoptesWorkflow($workflowId)
    {
        $workflow = Cache::remember(__METHOD__.$workflowId, 120, function () use ($workflowId) {
            $this->setProvider();
            $this->checkAccessToken('panoptes_token');
            $uri = $this->getWorkflowUri($workflowId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $result = $this->sendAuthorizedRequest($request);

            return $result['workflows'][0];
        });

        return $workflow;
    }

    /**
     * Get panoptes subject.
     *
     * @param $subjectId
     * @return null
     */
    public function getPanoptesSubject($subjectId)
    {
        $result = Cache::remember(__METHOD__.$subjectId, 120, function () use ($subjectId) {
            $this->setProvider();
            $this->checkAccessToken('panoptes_token');
            $uri = $this->getSubjectUri($subjectId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $results = $this->sendAuthorizedRequest($request);

            return isset($results['subjects'][0]) ? $results['subjects'][0] : null;
        });

        return $result;
    }

    /**
     * Get panoptes user.
     *
     * @param $userId
     * @return mixed
     */
    public function getPanoptesUser($userId)
    {
        $user = Cache::remember(__METHOD__.$userId, 120, function () use ($userId) {
            $this->setProvider();
            $this->checkAccessToken('panoptes_token');
            $uri = $this->getUserUri($userId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $results = $this->sendAuthorizedRequest($request);

            return isset($results['users'][0]) ? $results['users'][0] : null;
        });

        return $user;
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

                $uri = $this->buildClassificationCsvUri($expedition->panoptesProject->panoptes_workflow_id);
                $request = $this->buildAuthorizedRequest('POST', $uri, ['body' => '{"media":{"content_type":"text/csv"}}']);

                yield $expedition->id => $request;
            }
        };

        $this->setProvider();
        $this->checkAccessToken('panoptes_token');
        $responses = $this->poolBatchRequest($requests($expeditions));

        return $responses;
    }

    /**
     * Download csv panoptes classifications.
     *
     * @param $sources
     * @return array
     */
    public function panoptesClassificationsDownload($sources)
    {
        $requests = function () use ($sources)
        {
            foreach ($sources as $index => $source)
            {
                yield $index => function ($poolOpts) use ($source, $index)
                {
                    $reqOpts = [
                        'sink' => \Storage::path(config('config.nfn_downloads_classification') . '/' . $index . '.csv')
                    ];
                    if (is_array($poolOpts) && count($poolOpts) > 0)
                    {
                        $reqOpts = array_merge($poolOpts, $reqOpts); // req > pool
                    }

                    return $this->getHttpClient()->getAsync($source, $reqOpts);
                };
            }
        };

        $this->setProvider(false);
        $responses = $this->poolBatchRequest($requests());

        return $responses;
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

                $uri = $this->buildClassificationCsvUri($expedition->panoptesProject->panoptes_workflow_id);
                $request = $this->buildAuthorizedRequest('GET', $uri);

                yield $expedition->id => $request;
            }
        };

        $this->setProvider();
        $this->checkAccessToken('panoptes_token');
        $responses = $this->poolBatchRequest($requests($expeditions));

        return $responses;
    }
}