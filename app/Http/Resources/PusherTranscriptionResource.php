<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class PusherTranscriptionResource extends Resource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'project'              => $this->project,
            'description'          => $this->description,
            'guid'                 => $this->guid,
            'timestamp'            => \DateHelper::formatMongoDbDate($this->timestamp, 'Y-m-d\TH:i:s\Z'),
            'subject'              => $this->subject,
            'contributor'          => $this->contributor,
            'transcriptionContent' => $this->transcriptionContent,
            'links' => [
                'self' => route('wedigbiodashboards.show', ['guid' => $this->guid]),
            ],
        ];
    }
}
