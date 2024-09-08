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

namespace App\Transformers;

use App\Models\PusherTranscription as Model;
use Illuminate\Support\Facades\Config;
use League\Fractal\TransformerAbstract;

/**
 * Class PusherTranscriptionTransformer
 */
class PusherTranscriptionTransformer extends TransformerAbstract
{
    public function transform(Model $model)
    {
        return [
            'project' => $model->project,
            'description' => $model->description,
            'guid' => $model->guid,
            'timestamp' => $model->timestamp,
            'subject' => $model->subject,
            'contributor' => $model->contributor,
            'transcriptionContent' => $model->transcriptionContent,
            'discretionaryState' => $model->discretionaryState,
            'links' => [
                [
                    'rel' => 'self',
                    'uri' => 'https://'.Config::get('api.domain').'/wedigbiodashboards/'.$model->guid,
                ],
            ],
        ];
    }
}
