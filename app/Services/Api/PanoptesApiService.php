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
use Psr\Http\Message\RequestInterface;

/**
 * Class PanoptesApiService
 */
class PanoptesApiService extends HttpRequest
{
    private mixed $apiUri;

    private int $subject_count;

    private int $transcriptions_completed;

    private int $transcriptions_goal;

    private int $local_transcriptions_completed;

    private int $transcriber_count;

    private float $percent_completed;

    /**
     * PanoptesApiService constructor.
     */
    public function __construct()
    {
        $this->apiUri = config('zooniverse.panoptes.api_uri');
    }

    /**
     * Set config values.
     */
    public function getConfig(): array
    {
        return [
            'clientId' => config('zooniverse.panoptes.client_id'),
            'clientSecret' => config('zooniverse.panoptes.client_secret'),
            'redirectUri' => config('zooniverse.panoptes.redirect_uri'),
            'urlAccessToken' => config('zooniverse.panoptes.token_uri'),
            'scope' => config('zooniverse.panoptes.scopes'),
        ];
    }

    /**
     * Build authorized request.
     */
    public function buildAuthorizedRequest(string $method, string $uri, array $extra = []): RequestInterface
    {
        $options = array_merge([
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/vnd.api+json; version=1',
            ],
        ], $extra);

        return $this->buildAuthenticatedRequest($method, $uri, $options);
    }

    /**
     * Send authorized request.
     *
     * @throws GuzzleException
     */
    public function sendAuthorizedRequest($request): mixed
    {
        $response = $this->getHttpClient()->send($request);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get resource uri: projects, workflows, subjects, classifications, users
     */
    public function getPanoptesResourceUri($resource, $id, bool $export = false): string
    {
        return ! $export ? $this->apiUri.'/'.$resource.'/'.$id : $this->apiUri.'/'.$resource.'/'.$id.'/classifications_export';
    }

    /**
     * Get panoptes project.
     */
    public function getPanoptesProject($projectId): array
    {
        return Cache::remember(__METHOD__.$projectId, 120, function () use ($projectId) {
            $this->setHttpProvider($this->getConfig());
            $this->checkAccessToken('panoptes_token');
            $uri = $this->getPanoptesResourceUri('projects', $projectId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $result = $this->sendAuthorizedRequest($request);

            return $result['projects'][0];
        });
    }

    /**
     * Get panoptes workflow.
     */
    public function getPanoptesWorkflow($workflowId): mixed
    {
        return Cache::remember(__METHOD__.$workflowId, 120, function () use ($workflowId) {
            $this->setHttpProvider($this->getConfig());
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
     * @return null
     */
    public function getPanoptesSubject($subjectId)
    {
        return Cache::remember(__METHOD__.$subjectId, 120, function () use ($subjectId) {
            $this->setHttpProvider($this->getConfig());
            $this->checkAccessToken('panoptes_token');
            $uri = $this->getPanoptesResourceUri('subjects', $subjectId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $results = $this->sendAuthorizedRequest($request);

            return $results['subjects'][0] ?? null;
        });
    }

    /**
     * Get panoptes user.
     */
    public function getPanoptesUser($userId): mixed
    {
        return Cache::remember(__METHOD__.$userId, 120, function () use ($userId) {
            $this->setHttpProvider($this->getConfig());
            $this->checkAccessToken('panoptes_token');
            $uri = $this->getPanoptesResourceUri('users', $userId);
            $request = $this->buildAuthorizedRequest('GET', $uri);
            $results = $this->sendAuthorizedRequest($request);

            return $results['users'][0] ?? null;
        });
    }

    /**
     * Check needed variables.
     */
    public function checkForRequiredVariables($expedition): bool
    {
        return $expedition === null || ! isset($expedition->panoptesProject) || $expedition->panoptesProject->panoptes_workflow_id === null || $expedition->panoptesProject->panoptes_project_id === null;
    }

    /**
     * Calculates totals for transcripts and sets properties.
     */
    public function calculateTotals($workflow, $expeditionId)
    {
        $this->subject_count = (int) $workflow['subjects_count'];
        $this->transcriptions_goal = (int) $workflow['subjects_count'] * (int) $workflow['retirement']['options']['count'];
        $this->transcriptions_completed = (int) $workflow['classifications_count'];
        $this->local_transcriptions_completed = CountHelper::expeditionTranscriptionCount($expeditionId);
        $this->transcriber_count = CountHelper::expeditionTranscriberCount($expeditionId);
        $this->percent_completed = $this->percentCompleted();
    }

    /**
     * Calculate percent complete for transcriptsions.
     *
     * @return false|float|int
     */
    private function percentCompleted(): float|bool|int
    {
        $value = ($this->local_transcriptions_completed === 0) ? 0 : ($this->local_transcriptions_completed / $this->transcriptions_goal) * 100;

        return ($value > 100) ? 100 : round($value, 2);
    }

    /**
     * Return subject count.
     */
    public function getSubjectCount(): int
    {
        return $this->subject_count;
    }

    /**
     * Return transcriptions completed.
     */
    public function getTranscriptionsCompleted(): int
    {
        return $this->transcriptions_completed;
    }

    /**
     * Return transcriptions_goal.
     */
    public function getTranscriptionsGoal(): int
    {
        return $this->transcriptions_goal;
    }

    /**
     * Return local_transcriptions_completed.
     */
    public function getLocalTranscriptionsCompleted(): int
    {
        return $this->local_transcriptions_completed;
    }

    /**
     * Return transcriber count.
     */
    public function getExpeditionTranscriberCount(): int
    {
        return $this->transcriber_count;
    }

    /**
     * Return percent completed.
     */
    public function getPercentCompleted(): float
    {
        return $this->percent_completed;
    }

    /**
     * Get image url for subject.
     *
     * @return mixed|null
     */
    public function getSubjectImageLocation($subjectId): mixed
    {
        $subject = $this->getPanoptesSubject($subjectId);
        $locations = collect($subject['locations'])->filter(function ($location) {
            return ! empty($location['image/jpeg']);
        });

        return $locations->isNotEmpty() ? $locations->first()['image/jpeg'] : null;
    }
}
