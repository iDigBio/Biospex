<?php

namespace App\Repositories\Decorators;

use Illuminate\Contracts\Cache\Repository as Cache;
use App\Repositories\Contracts\Group as Contract;
use App\Repositories\GroupRepository;

class CacheGroupDecorator implements Contract
{

    /**
     * @var bool
     */
    protected $pass = false;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var
     */
    private $tag;

    /**
     * Constructor
     *
     * @param GroupRepository $repository
     * @param Cache $cache
     * @param $tag
     * @internal param Group $group
     */
    public function __construct(GroupRepository $repository, Cache $cache, $tag)
    {
        $this->repository = $repository;
        $this->cache = $cache;
        $this->tag = $tag;
    }

    /**
     * All
     *
     * @param array $columns
     * @return mixed
     */
    public function all()
    {
        if ($this->pass)
            return $this->repository->all();

        $key = md5('groups.all');

        return $this->cache->rememberForever($key, function() {
            return $this->repository->all();
        });
    }

    /**
     * Find
     *
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id)
    {
        if ($this->pass)
            return $this->repository->find($id);

        $key = md5('groups.all');

        return $this->cache->rememberForever($key, function() {
            return $this->repository->find($id);
        });
    }

    /**
     * Create record
     *
     * @param array $data
     * @return mixed
     */
    public function create($data)
    {
        $group = $this->group->create($data);
        $this->cache->flush();

        return $group;
    }

    /**
     * Update record
     *
     * @param array $input
     * @return mixed
     */
    public function update($data)
    {
        $group = $this->group->update($data);
        $this->cache->flush();

        return $group;
    }

    /**
     * Destroy records
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        $group = $this->group->destroy($id);
        $this->cache->flush();

        return $group;
    }

    /**
     * Find with eager loading
     *
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function findWith($id, $with)
    {
        $key = md5("group.$id." . implode(".", $with));

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        $group = $this->group->findWith($id, $with);

        if (! $this->pass) {
            $this->cache->put($key, $group);
        }

        return $group;
    }

    /**
     * Return a specific group by a given name
     *
     * @param  string $name
     * @return Group
     */
    public function byName($name)
    {
        $key = md5($name);

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        $group = $this->group->byName($name);

        if (! $this->pass) {
            $this->cache->put($key, $group);
        }

        return $group;
    }

    /**
     * Return groups with Admins optional and without Users for select options
     *
     * @param bool $admins
     * @return mixed
     */
    public function selectOptions($allGroups, $create = false)
    {
        $options = $this->group->selectOptions($allGroups, $create);

        return $options;
    }

    /**
     * Find all groups
     * @return mixed
     */
    public function findAllGroups()
    {
        $key = md5("groups");

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        $groups = $this->group->findAllGroups();

        if (! $this->pass) {
            $this->cache->put($key, $groups);
        }

        return $groups;
    }

    /**
     * Find all the groups depending on user
     *
     * @param array $allGroups
     * @return mixed
     */
    public function findAllGroupsWithProjects($allGroups = array())
    {
        foreach ($allGroups as $group) {
            $ids[] = $group->id;
        }
        $key = md5('groups.' . implode(".", $ids));

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        $groups = $this->group->findAllGroupsWithProjects($allGroups);

        if (! $this->pass) {
            $this->cache->put($key, $groups);
        }

        return $groups;
    }

    /**
     * Save
     *
     * @param $record
     * @return mixed
     */
    public function save($record)
    {
        $group = $this->group->save($record);
        $this->cache->flush();

        return $group;
    }

    public function setPass($value = false)
    {
        $this->pass = $value;
    }
}
