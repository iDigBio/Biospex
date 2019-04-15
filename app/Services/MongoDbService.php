<?php

namespace App\Services;

use Illuminate\Database\DatabaseManager;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

class MongoDbService
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @var Client
     */
    public $client;

    /**
     * Collection being accessed.
     * @var Collection
     */
    public $clientCollection;

    /**
     * MongoDbService constructor.
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Set connection client.
     */
    public function setClient()
    {
        $this->client = $this->databaseManager->connection('mongodb')->getMongoClient();
    }

    /**
     * Get client.
     *
     * @return Client
     */
    public function getClient()
    {
        if (empty($this->client))
        {
            $this->setClient();
        }

        return $this->client;
    }

    /**
     * Set database dynamically.
     *
     * @param null $database
     * @return null
     */
    public function setDatabase($database = null)
    {
        return null === $database ? config('database.connections.mongodb.database') : $database;
    }

    /**
     * Set mongo collection.
     *
     * @param $collection
     * @param null $database
     */
    public function setCollection($collection, $database = null)
    {
        if (empty($this->client)) {
            $this->setClient();
        }

        $this->clientCollection = $this->client->{$this->setDatabase($database)}->{$collection};
    }

    /**
     * Set a mongo id object.
     *
     * @param $value
     * @return \MongoDB\BSON\ObjectId
     */
    public function setMongoObjectId($value)
    {
        return new ObjectId($value);
    }

    /**
     * @param $criteria
     * @return int
     */
    public function count($criteria)
    {
        return $this->clientCollection->count($criteria);
    }

    /**
     * Find all matching query.
     *
     * @param array $query
     * @return mixed
     */
    public function find(array $query = [])
    {
        return $this->clientCollection->find($query);
    }

    /**
     * Find one matching query.
     *
     * @param array $query
     * @return array|null|object
     */
    public function findOne(array $query = [])
    {
        return $this->clientCollection->findOne($query);
    }

    /**
     * Find one and replace.
     *
     * @param $filter
     * @param $replacement
     * @return array|null|object
     */
    public function findOneAndReplace($filter, $replacement)
    {
        return $this->clientCollection->findOneAndReplace($filter, $replacement);
    }

    /**
     * @param array $attributes
     */
    public function insertOne(array $attributes = [])
    {
        $this->clientCollection->insertOne($attributes);
    }

    /**
     * Insert many documents.
     *
     * @param array $data
     */
    public function insertMany(array $data = [])
    {
        $this->clientCollection->insertMany($data);
    }

    /**
     * Update single record.
     *
     * @param array $attributes
     * @param $resourceId
     */
    public function updateOneById(array $attributes = [], $resourceId)
    {
        $this->clientCollection->updateOne(['_id' => $this->setMongoObjectId($resourceId)], ['$set' => $attributes]);
    }

    /**
     * Update many.
     *
     * @param array $attributes
     * @param array $criteria
     */
    public function updateMany(array $attributes, array $criteria)
    {
        $this->clientCollection->updateMany($criteria, $attributes);
    }

    /**
     * @param array $criteria
     */
    public function deleteMany(array $criteria)
    {
        $this->clientCollection->deleteMany($criteria);
    }

}