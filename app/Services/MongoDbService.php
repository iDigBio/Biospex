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
use Illuminate\Support\Facades\Cache;
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
     */
    public function setDatabase(?string $database = null): string
    {
        return $database === null ? config('database.connections.mongodb.database') : $database;
    }

    /**
     * Set mongo collection.
     *
     * @param  string  $collection
     * @param  string|null  $database
     */
    public function setCollection($collection, $database = null): void
    {
        if (empty($this->client)) {
            $this->setClient();
        }

        $this->clientCollection = $this->client->{$this->setDatabase($database)}->{$collection};
    }

    /**
     * Set a mongo id object.
     *
     * @param  mixed  $value
     */
    public function setMongoObjectId($value): \MongoDB\BSON\ObjectId
    {
        return new ObjectId($value);
    }

    /**
     * Set regex value.
     *
     * @param  string  $value
     */
    public function setRegex($value): \MongoDB\BSON\Regex
    {
        return new Regex($value, 'i');
    }

    public function count(array $filter = [], array $options = []): int
    {
        $key = 'mongodb_count:'.$this->clientCollection->getCollectionName().':'.md5(serialize([$filter, $options]));
        $tags = ['mongodb', $this->clientCollection->getCollectionName()];

        return Cache::tags($tags)->remember($key, 1800, function () use ($filter, $options) {
            return $this->clientCollection->countDocuments($filter, $options);
        });
    }

    /**
     * Find all matching query.
     */
    public function find(array $query = [], array $options = []): mixed
    {
        $key = 'mongodb_find:'.$this->clientCollection->getCollectionName().':'.md5(serialize([$query, $options]));
        $tags = ['mongodb', $this->clientCollection->getCollectionName()];

        return Cache::tags($tags)->remember($key, 1800, function () use ($query, $options) {
            return $this->getArray($this->clientCollection->find($query, $options));
        });
    }

    /**
     * Find one matching query.
     */
    public function findOne(array $query = []): ?array
    {
        $key = 'mongodb_find_one:'.$this->clientCollection->getCollectionName().':'.md5(serialize($query));
        $tags = ['mongodb', $this->clientCollection->getCollectionName()];

        return Cache::tags($tags)->remember($key, 1800, function () use ($query) {
            $result = $this->clientCollection->findOne($query);

            return $result?->toArray();
        });
    }

    /**
     * Find one and replace.
     *
     * @param  array  $filter
     * @param  array  $replacement
     * @param  array  $options
     */
    public function findOneAndReplace($filter, $replacement, $options = []): array|null|object
    {
        return $this->clientCollection->findOneAndReplace($filter, $replacement, $options);
    }

    /**
     * Insert one record.
     */
    public function insertOne(array $attributes = []): \MongoDB\InsertOneResult
    {
        return $this->clientCollection->insertOne($attributes);
    }

    /**
     * Insert many documents.
     */
    public function insertMany(array $data = []): void
    {
        $this->clientCollection->insertMany($data);
    }

    /**
     * Update single record.
     *
     * @param  mixed  $resourceId
     */
    public function updateOneById(array $attributes, $resourceId): void
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

    /**
     * Delete many documents.
     */
    public function deleteMany(array $criteria): void
    {
        $this->clientCollection->deleteMany($criteria);
    }

    /**
     * Aggregate documents.
     *
     * @param  array  $pipline
     * @param  array  $options
     */
    public function aggregate($pipline, $options = []): mixed
    {
        $key = 'mongodb_aggregate:'.$this->clientCollection->getCollectionName().':'.md5(serialize([$pipline, $options]));
        $tags = ['mongodb', $this->clientCollection->getCollectionName()];

        return Cache::tags($tags)->remember($key, 1800, function () use ($pipline, $options) {
            return $this->getArray($this->clientCollection->aggregate($pipline, $options));
        });
    }

    /**
     * Return only id from results.
     *
     * @param  mixed  $cursor
     */
    public function pluckId($cursor): array
    {
        $ids = [];
        foreach ($cursor as $doc) {
            $ids[] = (string) $doc->_id;
        }

        return $ids;
    }
}
