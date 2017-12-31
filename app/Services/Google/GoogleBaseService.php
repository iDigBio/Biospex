<?php

namespace App\Services\Google;

use PulkitJalan\Google\Facades\Google;

class GoogleBaseService
{

    /**
     * Create Google Service.
     *
     * @param $service
     * @return \Google_Service
     * @throws \Exception
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