<?php

namespace app\Repositories\Decorators;

use App\Repositories\Contracts\Permission;
use App\Repositories\PermissionRepository;
use Illuminate\Contracts\Cache\Repository as Cache;

class CachePermissionDecorator implements Permission
{
    /**
     * Set whether cache is bypassed.
     *
     * @var bool
     */
    public $pass = false;

    /**
     * @var Permission
     */
    private $permission;

    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var
     */
    private $tag;

    /**
     * @param PermissionRepository $repository
     * @param Cache $cache
     * @param $tag
     * @internal param Permission $permission
     */
    public function __construct(PermissionRepository $repository, Cache $cache, $tag)
    {

        $this->$repository = $repository;
        $this->cache = $cache;
        $this->tag = $tag;
    }

    public function all()
    {
        if ($this->pass)
            return $this->repository->all();

        $key = md5('permissions.all');

        return $this->cache->tags($this->tag)->rememberForever($key, function() {
            return $this->repository->all();
        });
    }

    public function find($id)
    {
        if ($this->pass)
            return $this->repository->find($id);

        $key = md5('permissions.' . $id);

        return $this->cache->tags($this->tag)->rememberForever($key, function($id) {
            return $this->repository->find($id);
        });
    }

    public function findWith($id, $with)
    {
        return;
    }

    public function save($record)
    {
        return;
    }

    public function create($data)
    {
        return;
    }

    public function update($data)
    {
        return;
    }

    public function destroy($id)
    {
        return;
    }

    // Methods contained implemented in interface must exist here
    public function getPermissionsGroupBy()
    {
        return $this->model->getPermissionsGroupBy();
    }

    public function setPermissions(array $data)
    {
        return $this->model->setPermissions($data);
    }
}