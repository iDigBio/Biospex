<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PusherTranscriptionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => PusherTranscriptionResource::collection($this->collection)
        ];
    }

    public function with($request)
    {
        return [
            'links'    => [
                'self' => route('wedigbiodashboards.index'),
            ],
        ];
    }
}
