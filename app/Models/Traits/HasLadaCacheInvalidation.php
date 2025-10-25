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

namespace App\Models\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

trait HasLadaCacheInvalidation
{
    /**
     * Boot the trait and hook model events for auto-flush.
     */
    public static function bootHasLadaCacheInvalidation()
    {
        static::created(function ($model) {
            $model->flushLadaCache('create');
        });

        static::updated(function ($model) {
            $model->flushLadaCache('update');
        });

        static::deleting(function ($model) {
            $model->flushLadaCache('delete');
        });
    }

    /**
     * Manual full flush of Lada-cache keys (prefix-scoped).
     *
     * @param  string  $action  // 'create', 'update', 'delete' for logging
     */
    public function flushLadaCache(string $action = 'manual'): void
    {
        try {
            $redisConnection = config('lada-cache.redis_connection', 'cache');
            $prefix = config('lada-cache.prefix', 'lada:');
            $redis = Redis::connection($redisConnection);

            // Use Redis config for DB index (not model's connection)
            $dbIndex = config("database.redis.{$redisConnection}.database", 0);
            $redis->select($dbIndex);

            $keys = $redis->keys($prefix.'*');
            if (! empty($keys)) {
                $redis->del($keys);
                Log::info("Lada-cache flushed via trait ({$action})", [
                    'model' => get_class($this),
                    'id' => $this->id ?? 'new',
                    'table' => $this->getTable(),
                    'keys_deleted' => count($keys),
                    'prefix' => $prefix,
                ]);
            } else {
                Log::info("No Lada keys to flush via trait ({$action})", [
                    'model' => get_class($this),
                    'prefix' => $prefix,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("Lada-cache flush failed via trait ({$action})", [
                'model' => get_class($this),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
