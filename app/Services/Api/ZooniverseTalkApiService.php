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

namespace App\Services\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Class ZooniverseTalkApiService
 */
class ZooniverseTalkApiService
{
    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public mixed $talk_api_uri;

    public string $resource_uri;

    /**
     * Api construct
     */
    public function __construct()
    {
        $this->talk_api_uri = config('zooniverse.talk_api_uri');
    }

    /**
     * Get talk comments for project and subject.
     */
    public function getComments(int $projectId, int $subjectId): mixed
    {
        $this->setResourceUri($projectId, $subjectId);

        $key = "zooniverse_talk_comments:{$projectId}:{$subjectId}";
        $tags = ['zooniverse_talk', 'comments'];

        $talk = Cache::tags($tags)->remember($key, 3600, function () {
            $response = Http::get($this->resource_uri);

            return $response->json();
        });

        return $talk['comments'];
    }

    /**
     * Set resource uri with project id and subject id.
     */
    private function setResourceUri(int $projectId, int $subjectId)
    {
        $trans = ['PROJECT_ID' => $projectId, 'SUBJECT_ID' => $subjectId];
        $this->resource_uri = strtr($this->talk_api_uri, $trans);
    }
}
