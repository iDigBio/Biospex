<?php namespace App\Services\Cache;

/**
 * LaravelCache.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

use Illuminate\Contracts\Cache\Repository as Repository;

class LaravelCache implements Cache
{
    /**
     * @var Illuminate\Cache\CacheManager
     */
    protected $cache;

    /**
     * @var string
     */
    protected $tag;

    /**
     * @var integer
     */
    protected $minutes;

    /**
     * Construct
     *
     * @param Illuminate\Cache\CacheManager $cache
     * @param string $tag
     * @param integer $minutes
     */
    public function __construct(Repository $cache, $tag, $minutes = 60)
    {
        $this->cache = $cache;
        $this->tag = $tag;
        $this->minutes = $minutes;
    }

    /**
     * Get
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->cache->tags($this->tag)->get($key);
    }

    /**
     * Put
     *
     * @param string $key
     * @param mixed $value
     * @param integer $minutes
     * @return mixed
     */
    public function put($key, $value, $minutes = null)
    {
        if (is_null($minutes)) {
            $minutes = $this->minutes;
        }

        return $this->cache->tags($this->tag)->put($key, $value, $minutes);
    }

    /**
     * Has
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->cache->tags($this->tag)->has($key);
    }

    /**
     * Flush
     *
     * @return mixed
     */
    public function flush()
    {
        return $this->cache->tags($this->tag)->flush();
    }
}
