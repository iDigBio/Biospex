<?php

namespace App\Services\Api;

use Cache;

class NfnApiService extends NfnApi
{
    /**
     * Get nfn project.
     *
     * @param $projectId
     * @return array
     */
    public function getNfnProject($projectId)
    {
        $project = Cache::remember(__METHOD__.$projectId, 120, function () use ($projectId) {
            $this->setProvider();
            $this->checkAccessToken('nfnToken');
            $uri = $this->getProjectUri($projectId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $result = $this->sendAuthorizedRequest($request);

            return $result['projects'][0];
        });

        return $project;
    }

    /**
     * Get nfn workflow.
     *
     * @param $workflowId
     * @return mixed
     */
    public function getNfnWorkflow($workflowId)
    {
        $workflow = Cache::remember(__METHOD__.$workflowId, 120, function () use ($workflowId) {
            $this->setProvider();
            $this->checkAccessToken('nfnToken');
            $uri = $this->getWorkflowUri($workflowId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $result = $this->sendAuthorizedRequest($request);

            return $result['workflows'][0];
        });

        return $workflow;
    }

    /**
     * Get nfn subject.
     *
     * @param $subjectId
     * @return null
     */
    public function getNfnSubject($subjectId)
    {
        $result = Cache::remember(__METHOD__.$subjectId, 120, function () use ($subjectId) {
            $this->setProvider();
            $this->checkAccessToken('nfnToken');
            $uri = $this->getSubjectUri($subjectId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $results = $this->sendAuthorizedRequest($request);

            return isset($results['subjects'][0]) ? $results['subjects'][0] : null;
        });

        return $result;
    }

    /**
     * Get nfn user.
     *
     * @param $userId
     * @return mixed
     */
    public function getNfnUser($userId)
    {
        $user = Cache::remember(__METHOD__.$userId, 120, function () use ($userId) {
            $this->setProvider();
            $this->checkAccessToken('nfnToken');
            $uri = $this->getUserUri($userId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $results = $this->sendAuthorizedRequest($request);

            return isset($results['users'][0]) ? $results['users'][0] : null;
        });

        return $user;
    }

    /**
     * Send requests to build nfn classifications download.
     *
     * @param $expeditions
     * @return array
     */
    public function nfnClassificationCreate($expeditions)
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

                $uri = $this->buildClassificationCsvUri($expedition->nfnWorkflow->panoptes_workflow_id);
                $request = $this->buildAuthorizedRequest('POST', $uri, ['body' => '{"media":{"content_type":"text/csv"}}']);

                yield $expedition->id => $request;
            }
        };

        $this->setProvider();
        $this->checkAccessToken('nfnToken');
        $responses = $this->poolBatchRequest($requests($expeditions));

        return $responses;
    }

    /**
     * Download csv nfn classifications.
     *
     * @param $sources
     * @return array
     */
    public function nfnClassificationsDownload($sources)
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
     * Check nfn classifications file to see if ready for download.
     *
     * @param $expeditions
     * @return array
     */
    public function nfnClassificationsFile($expeditions)
    {
        $requests = function ($expeditions)
        {
            foreach ($expeditions as $expedition)
            {
                if ($this->checkForRequiredVariables($expedition))
                {
                    continue;
                }

                $uri = $this->buildClassificationCsvUri($expedition->nfnWorkflow->panoptes_workflow_id);
                $request = $this->buildAuthorizedRequest('GET', $uri);

                yield $expedition->id => $request;
            }
        };

        $this->setProvider();
        $this->checkAccessToken('nfnToken');
        $responses = $this->poolBatchRequest($requests($expeditions));

        return $responses;
    }
}