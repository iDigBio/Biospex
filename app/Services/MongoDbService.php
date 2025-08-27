<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services;

use Illuminate\Database\DatabaseManager;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\Collection;

/**
 * Class MongoDbService
 */
class MongoDbService
{
    public Client $client;

    /**
     * Collection being accessed.
     */
    public Collection $clientCollection;

    /**
     * MongoDbService constructor.
     */
    public function __construct(protected DatabaseManager $databaseManager) {}

    /**
     * Return the cursor as an array.
     */
    public function getArray($cursor): mixed
    {
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);

        return $cursor->toArray();
    }

    /**
     * Set a connection client.
     */
    public function setClient(): void
    {
        /** @var \MongoDB\Laravel\Connection $connection */
        $this->client = $this->databaseManager->connection('mongodb')->getClient();
    }

    /**
     * Set database dynamically.
     *
     * @param  null  $database
     */
    public function setDatabase($database = null): null
    {
        return $database === null ? config('database.connections.mongodb.database') : $database;
    }

    /**
     * Set mongo collection.
     *
     * @param  null  $database
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
     * @return \MongoDB\BSON\ObjectId
     */
    public function setMongoObjectId($value)
    {
        return new ObjectId($value);
    }

    /**
     * Set regex value.
     *
     * @return \MongoDB\BSON\Regex
     */
    public function setRegex($value)
    {
        return new Regex($value, 'i');
    }

    public function count(array $filter = [], array $options = []): int
    {
        $key = AutoCacheHelper::generateKey('mongodb_count', [
            'collection' => $this->clientCollection->getCollectionName(),
            'filter' => $filter,
            'options' => $options,
        ]);
        $tags = AutoCacheHelper::generateTags(['mongodb', $this->clientCollection->getCollectionName()]);

        return AutoCacheHelper::remember($key, 1800, function () use ($filter, $options) {
            return $this->clientCollection->countDocuments($filter, $options);
        }, $tags);
    }

    /**
     * Find all matching query.
     *
     * @return mixed
     */
    public function find(array $query = [], array $options = [])
    {
        $key = AutoCacheHelper::generateKey('mongodb_find', [
            'collection' => $this->clientCollection->getCollectionName(),
            'query' => $query,
            'options' => $options,
        ]);
        $tags = AutoCacheHelper::generateTags(['mongodb', $this->clientCollection->getCollectionName()]);

        return AutoCacheHelper::remember($key, 1800, function () use ($query, $options) {
            return $this->getArray($this->clientCollection->find($query, $options));
        }, $tags);
    }

    /**
     * Find one matching query.
     *
     * @return array|null|object
     */
    public function findOne(array $query = [])
    {
        $key = AutoCacheHelper::generateKey('mongodb_find_one', [
            'collection' => $this->clientCollection->getCollectionName(),
            'query' => $query,
        ]);
        $tags = AutoCacheHelper::generateTags(['mongodb', $this->clientCollection->getCollectionName()]);

        return AutoCacheHelper::remember($key, 1800, function () use ($query) {
            $result = $this->clientCollection->findOne($query);

            return $result ? $result->toArray() : null;
        }, $tags);
    }

    /**
     * Find one and replace.
     *
     * @param  array  $options
     * @return array|null|object
     */
    public function findOneAndReplace($filter, $replacement, $options = [])
    {
        return $this->clientCollection->findOneAndReplace($filter, $replacement, $options);
    }

    /**
     * Insert one record.
     *
     * @return \MongoDB\InsertOneResult
     */
    public function insertOne(array $attributes = [])
    {
        return $this->clientCollection->insertOne($attributes);
    }

    /**
     * Insert many documents.
     */
    public function insertMany(array $data = [])
    {
        $this->clientCollection->insertMany($data);
    }

    /**
     * Update single record.
     */
    public function updateOneById(array $attributes, $resourceId)
    {
        $this->clientCollection->updateOne(['_id' => $this->setMongoObjectId($resourceId)], ['$set' => $attributes]);
    }

    /**
     * Update many.
     */
    public function updateMany(array $criteria, array $attributes): \MongoDB\UpdateResult
    {
        return $this->clientCollection->updateMany($criteria, $attributes);
    }

    public function deleteMany(array $criteria)
    {
        $this->clientCollection->deleteMany($criteria);
    }

    public function aggregate($pipline, $options = [])
    {
        $key = AutoCacheHelper::generateKey('mongodb_aggregate', [
            'collection' => $this->clientCollection->getCollectionName(),
            'pipeline' => $pipline,
            'options' => $options,
        ]);
        $tags = AutoCacheHelper::generateTags(['mongodb', $this->clientCollection->getCollectionName()]);

        return AutoCacheHelper::remember($key, 1800, function () use ($pipline, $options) {
            return $this->getArray($this->clientCollection->aggregate($pipline, $options));
        }, $tags);
    }

    /**
     * Return only id from results.
     *
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
