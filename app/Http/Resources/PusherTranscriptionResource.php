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

namespace App\Http\Resources;

use DateHelper;
use Illuminate\Http\Resources\Json\JsonResource ;

/**
 * Class PusherTranscriptionResource
 *
 * @package App\Http\Resources
 */
class PusherTranscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'project'              => $this->project,
            'description'          => $this->description,
            'guid'                 => $this->guid,
            'timestamp'            => $this->timestamp,
            'subject'              => $this->subject,
            'contributor'          => $this->contributor,
            'transcriptionContent' => $this->transcriptionContent,
            'links' => [
                'self' => route('wedigbiodashboards.show', ['guid' => $this->guid]),
            ],
        ];
    }
}
