<?php

namespace App\Transformers;

use App\Models\WeDigBioDashboard as Model;
use Illuminate\Support\Facades\Config;
use League\Fractal\TransformerAbstract;

class WeDigBioDashboardTransformer extends TransformerAbstract
{

    public function transform(Model $model)
    {
        return [
            'project'              => $model->project,
            'description'          => $model->description,
            'guid'                 => $model->guid,
            'timestamp'            => $model->timestamp,
            'subject'              => $model->subject,
            'contributor'          => $model->contributor,
            'transcriptionContent' => $model->transcriptionContent,
            'discretionaryState'   => $model->discretionaryState,
            'links'                => [
                [
                    'rel' => 'self',
                    'uri' => 'https://' . Config::get('api.domain') . '/wedigbiodashboards/' . $model->guid,
                ]
            ]
        ];
    }
}