<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class WeDigBioDashboardCollection
 */
class WeDigBioDashboardCollection extends ResourceCollection
{
    /**
     * @var string
     */
    public static $wrap = 'items';

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = 'App\Http\Resources\WeDigBioDashboard';

    /**
     * @var string
     */
    protected $collectionRoute;

    /**
     * @var string
     */
    protected $resourceRoute;

    /**
     * Add for routing.
     *
     * @return $this
     */
    public function collectionRoute($collectionRoute)
    {
        $this->collectionRoute = $collectionRoute;

        return $this;
    }

    /**
     * Add for routing.
     *
     * @return $this
     */
    public function resourceRoute($resourceRoute)
    {
        $this->resourceRoute = $resourceRoute;

        return $this;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (WeDigBioDashboard $transcription) {
            return (new WeDigBioDashboard($transcription))->resourceRoute('api.v1.wedigbio-dashboard.show');
        });

        return parent::toArray($request);
    }

    public function with($request)
    {
        return [
            'links' => [
                'self' => route($this->collectionRoute),
            ],
        ];
    }
}
