<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services;

use Illuminate\Database\DatabaseManager;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\BSON\Regex;

/**
 * Class MongoDbService
 *
 * @package App\Services
 */
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
     *
     * @var Collection
     */
    public $clientCollection;

    /**
     * MongoDbService constructor.
     *
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Return cursor as array.
     *
     * @param $cursor
     * @return mixed
     */
    public function getArray($cursor)
    {
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);

        return $cursor->toArray();
    }

    /**
     * Set connection client.
     */
    public function setClient()
    {
        $this->client = $this->databaseManager->connection('mongodb')->getMongoClient();
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
     * Set regex value.
     *
     * @param $value
     * @return \MongoDB\BSON\Regex
     */
    public function setRegex($value)
    {
        return new Regex($value, 'i');
    }

    /**
     * @param array $filter
     * @param array $options
     * @return int
     */
    public function count(array $filter = [], array $options = []): int
    {
        return $this->clientCollection->countDocuments($filter, $options);
    }

    /**
     * Find all matching query.
     *
     * @param array $query
     * @param array $options
     * @return mixed
     */
    public function find(array $query = [], array $options = [])
    {
        return $this->clientCollection->find($query, $options);
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
     * @param array $options
     * @return array|null|object
     */
    public function findOneAndReplace($filter, $replacement, $options = [])
    {
        return $this->clientCollection->findOneAndReplace($filter, $replacement, $options);
    }

    /**
     * Insert one record.
     *
     * @param array $attributes
     * @return \MongoDB\InsertOneResult
     */
    public function insertOne(array $attributes = [])
    {
        return $this->clientCollection->insertOne($attributes);
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
    public function updateOneById(array $attributes, $resourceId)
    {
        $this->clientCollection->updateOne(['_id' => $this->setMongoObjectId($resourceId)], ['$set' => $attributes]);
    }

    /**
     * Update many.
     *
     * @param array $attributes
     * @param array $criteria
     * @return \MongoDB\UpdateResult
     */
    public function updateMany(array $attributes, array $criteria): \MongoDB\UpdateResult
    {
        return $this->clientCollection->updateMany($criteria, $attributes);
    }

    /**
     * @param array $criteria
     */
    public function deleteMany(array $criteria)
    {
        $this->clientCollection->deleteMany($criteria);
    }

    public function aggregate($pipline, $options = [])
    {
        return $this->clientCollection->aggregate($pipline, $options);
    }

    /**
     * Return only id from results.
     *
     * @param $cursor
     * @return array
     */
    public function pluckId($cursor)
    {
        $ids = [];
        foreach ($cursor as $doc) {
            $ids[] = (string) $doc->_id;
        }

        return $ids;
    }
}