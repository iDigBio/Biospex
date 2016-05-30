<?php

namespace App\Repositories\Contracts;


/**
 * Interface CacheableInterface.
 * 
 * @package App\Repositories\Contracts
 */
interface CacheableInterface
{

    /**
     * Return instance of Cache Repository.
     * 
     * @return mixed
     */
    public function getCacheRepository();

    /**
     * Get Cache key for the method.
     * 
     * @param $method
     * @param null $args
     * @return mixed
     */
    public function createCacheKey($method, $args = null);

    /**
     * Get cache minutes.
     * 
     * @return mixed
     */
    public function getCacheMinutes();

    /**
     * Skip Cache.
     * 
     * @param bool $status
     * @return mixed
     */
    public function skipCache($status = true);
}