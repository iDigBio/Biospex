<?php

namespace App\Services\Google;


use App\Services\BaseService;
use PulkitJalan\Google\Facades\Google;

class GoogleBaseService extends BaseService
{
    /**
     * Create a Google service and return;
     *
     * @param $service
     * @return mixed
     */
    public function makeService($service)
    {

        return Google::make($service);
    }

    /**
     * Set values for given service (i.e. Table, Permissions, Column).
     *
     * @param $service
     * @param array $property
     * @return mixed
     */
    public function setServiceProperties($service, $property)
    {
        $service = $this->makeService($service);
        foreach ($property as $method => $value)
        {
            if (method_exists($service, $method))
            {
                $service->{$method}($value);
            }
        }

        return $service;
    }
}