<?php
namespace App\Repositories\Traits;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Class CacheableRepository
 * @package App\Repositories\Traits
 */
trait CacheableRepository
{

    /**
     * @var bool
     */
    protected $cacheSkip = false;

    /**
     * @var
     */
    protected $cacheRepository;

    /**
     * Return instance of Cache Repository.
     * 
     * @return \Illuminate\Foundation\Application|mixed
     */
    public function getCacheRepository()
    {
        if ($this->cacheRepository === null) {
            $this->cacheRepository = app(CacheRepository::class);
        }
        
        return $this->cacheRepository;
    }

    /**
     * Get Cache key for the method.
     *
     * @param $method
     * @param null $args
     * @return string
     */
    public function createCacheKey($method, $args = null)
    {
        $serialized = md5(serialize($args) . $this->serializeWith());
                
        return sprintf('%s@%s-%s', get_called_class(), $method, $serialized);
    }

    /**
     * Get cache minutes.
     *
     * @return int
     */
    public function getCacheMinutes()
    {
        return isset($this->cacheMinutes) ? $this->cacheMinutes : config('config.cache_minutes');
    }

    /**
     * Skip Cache.
     *
     * @param bool $status
     * @return $this
     */
    public function skipCache($status = true)
    {
        $this->cacheSkip = $status;

        return $this;
    }

    /**
     * Retrieve records using get.
     *
     * @param array $columns
     * @return mixed
     */
    public function get(array $columns = ['*'])
    {
        if ($this->cacheSkip)
        {
            return parent::get($columns);
        }

        $key = $this->createCacheKey('get', func_get_args());

        $minutes = $this->getCacheMinutes();

        return $this->getCacheRepository()->remember($key, $minutes, function () use ($columns)
        {
            return parent::get($columns);
        });
    }

    /**
     * Retrieve first record.
     *
     * @param array $columns
     * @return mixed
     */
    public function first(array $columns = ['*'])
    {
        if ($this->cacheSkip)
        {
            return parent::first($columns);
        }

        $key = $this->createCacheKey('first', func_get_args());

        $minutes = $this->getCacheMinutes();

        return $this->getCacheRepository()->remember($key, $minutes, function () use ($columns)
        {
            return parent::first($columns);
        });
    }

    /**
     * Retrieve all data of repository.
     *
     * @param array $columns
     * @return mixed
     */
    public function all(array $columns = ['*'])
    {
        if ($this->cacheSkip)
        {
            return parent::all($columns);
        }
        
        $key = $this->createCacheKey('all', func_get_args());

        $minutes = $this->getCacheMinutes();
        
        return $this->getCacheRepository()->remember($key, $minutes, function () use ($columns)
        {
            return parent::all($columns);
        });

    }

    /**
     * Find data by id.
     *
     * @param       $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, array $columns = ['*'])
    {
        if ($this->cacheSkip)
        {
            return parent::find($id, $columns);
        }

        $key = $this->createCacheKey('find', func_get_args());
        
        $minutes = $this->getCacheMinutes();

        return $this->getCacheRepository()->remember($key, $minutes, function () use ($id, $columns)
        {
            return parent::find($id, $columns);
        });
    }

    /**
     * Retrieve all data of repository, paginated.
     *
     * @param null $limit
     * @param array $columns
     * @return mixed
     */
    public function paginate($limit = null, array $columns = ['*'])
    {
        if ($this->cacheSkip)
        {
            return parent::paginate($limit, $columns);
        }

        $key = $this->createCacheKey('paginate', func_get_args());
        $minutes = $this->getCacheMinutes();

        return $this->getCacheRepository()->remember($key, $minutes, function () use ($limit, $columns)
        {
            return parent::paginate($limit, $columns);
        });

    }

    /**
     * Return list.
     *
     * @param $value
     * @param $index
     * @return mixed
     */
    public function lists($value, $index)
    {
        if ($this->cacheSkip)
        {
            return parent::lists($value, $index);
        }

        $key = $this->createCacheKey('lists', func_get_args());
        $minutes = $this->getCacheMinutes();

        return $this->getCacheRepository()->remember($key, $minutes, function () use ($value, $index)
        {
            return parent::lists($value, $index);
        });
    }

    /**
     * Serialize the with array.
     *
     * @return string
     */
    protected function serializeWith()
    {
        return serialize($this->getWith());
    }
}