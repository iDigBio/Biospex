<?php

namespace App\Services;

use Illuminate\Database\DatabaseManager;
use MongoClient;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

class MongoDbService
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @var MongoClient
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
     * @return \MongoClient
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
     * @param $collection
     */
    public function setCollection($collection)
    {
        if (empty($this->client))
        {
            $this->setClient();
        }

        $this->clientCollection = $this->client->{config('database.connections.mongodb.database')}->{$collection};
    }

    /**
     * @param array $query
     * @return mixed
     */
    public function find(array $query = [])
    {
        return $this->clientCollection->find($query);
    }

    /**
     * @param array $attributes
     */
    public function insertOne(array $attributes = [])
    {
        $this->clientCollection->insertOne($attributes);
    }

    /**
     * @param array $attributes
     * @param $resourceId
     */
    public function updateOne(array $attributes = [], $resourceId)
    {
        $this->clientCollection->updateOne(
            ['_id' => $resourceId],
            ['$set' => $attributes]
        );
    }

    /**
     * @param array $attributes
     * @param $resourceId
     */
    public function updateOneById(array $attributes = [], $resourceId)
    {
        $this->clientCollection->updateOne(
            ['_id' => new ObjectId($resourceId)],
            ['$set' => $attributes]
        );
    }

    public function updateMany(array $attributes, array $criteria)
    {
        $this->clientCollection->updateMany($criteria, $attributes);
    }

    /**
     * @param array $query
     */
    public function deleteOne(array $query)
    {
        $this->clientCollection->deleteOne($query);
    }
    
}