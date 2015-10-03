<?php namespace App\Repositories\Decorators;

use App\Repositories\Contracts\Expedition;

abstract class AbstractExpeditionDecorator implements Expedition
{
    /**
     * @var Expedition
     */
    protected $expedition;

    public function __construct(Expedition $expedition)
    {
        $this->expedition = $expedition;
    }

    /**
     * Return all
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        return $this->expedition->all($columns);
    }

    /**
     * Find by id
     *
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->expedition->find($id, $columns);
    }

    /**
     * Create record
     *
     * @param array $data
     * @return mixed
     */
    public function create($data = [])
    {
        return $this->expedition->create($data);
    }

    /**
     * Update record
     *
     * @param array $input
     * @return mixed
     */
    public function update($data = [])
    {
        return $this->expedition->update($data);
    }

    /**
     * Destroy records
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->expedition->destroy($id);
    }

    /**
     * Find with eager loading
     *
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function findWith($id, $with = [])
    {
        return $this->expedition->findWith($id, $with);
    }

    /**
     * Find by uuid.
     *
     * @param $uuid
     * @return mixed
     */
    public function findByUuid($uuid)
    {
        return $this->expedition->findByUuid($uuid);
    }
}
