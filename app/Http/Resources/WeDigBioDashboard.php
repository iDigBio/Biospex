<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class WeDigBioDashboard
 *
 * @package App\Http\Resources
 */
class WeDigBioDashboard extends JsonResource
{
    /**
     * @var string
     */
    public static $wrap = 'items';

    /**
     * @var string
     */
    protected $resourceRoute;

    /**
     * Add for building route.
     *
     * @param string $resourceRoute
     * @return $this
     */
    public function resourceRoute(string $resourceRoute){
        $this->resourceRoute = $resourceRoute;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'project'              => $this->project,
            'description'          => $this->description,
            'guid'                 => $this->guid,
            'timestamp'            => $this->timestamp,
            'subject'              => $this->subject,
            'contributor'          => $this->contributor,
            'transcriptionContent' => $this->transcriptionContent,
            'discretionaryState'   => $this->discretionaryState,
            'links' => [
                'self' => route($this->resourceRoute, ['wedigbio_dashboard' => $this->guid]),
            ],
        ];
    }
}
