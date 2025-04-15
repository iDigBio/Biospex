<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class WeDigBioDashboard
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
     * @return $this
     */
    public function resourceRoute(string $resourceRoute)
    {
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
        $contributor = $this->contributor;

        if (isset($contributor['decimalLatitude'])) {
            $contributor['decimalLatitude'] = (float) $contributor['decimalLatitude'];
        }

        if (isset($contributor['decimalLongitude'])) {
            $contributor['decimalLongitude'] = (float) $contributor['decimalLongitude'];
        }

        return [
            'project' => $this->project,
            'description' => $this->description,
            'guid' => $this->guid,
            'timestamp' => $this->timestamp,
            'subject' => $this->subject,
            'contributor' => $contributor,
            'transcriptionContent' => $this->transcriptionContent,
            'discretionaryState' => $this->discretionaryState,
            'links' => [
                'self' => route($this->resourceRoute, ['wedigbio_dashboard' => $this->guid]),
            ],
        ];
    }
}
