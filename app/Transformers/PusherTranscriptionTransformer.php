<?php

namespace App\Transformers;

use App\Models\PusherTranscription as Model;
use Illuminate\Support\Facades\Config;
use League\Fractal\TransformerAbstract;

class PusherTranscriptionTransformer extends TransformerAbstract
{

    public function transform(Model $model)
    {
        return [
            'project'              => $model->project,
            'description'          => $model->description,
            'guid'                 => $model->guid,
            'timestamp'            => \DateHelper::formatMongoDbDate($model->timestamp, 'Y-m-d\TH:i:s\Z'),
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