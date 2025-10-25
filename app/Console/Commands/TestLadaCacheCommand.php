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

namespace App\Console\Commands;

use App\Models\Expedition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class TestLadaCacheCommand extends Command
{
    protected $signature = 'test:lada-cache {--model=Expedition} {--cleanup}';

    protected $description = 'Test Lada Cache functionality with model operations';

    private string $cachePrefix;

    private string $redisConnection;

    public function __construct()
    {
        parent::__construct();
        $this->cachePrefix = config('lada-cache.prefix', 'lada:');
        $this->redisConnection = config('lada-cache.redis_connection', 'cache');
    }

    public function handle(): int
    {
        if (! config('lada-cache.active', true)) {
            $this->error('❌ Lada Cache is disabled in configuration');

            return self::FAILURE;
        }

        $this->info('🧪 Testing Lada Cache with Expedition model...');
        $this->newLine();

        // Cleanup if requested
        if ($this->option('cleanup')) {
            $this->cleanupTestData();
        }

        // Step 1: Test cache population
        $this->testCachePopulation();

        // Step 2: Test cache invalidation on create
        $this->testCreateInvalidation();

        // Step 3: Test cache invalidation on update
        $this->testUpdateInvalidation();

        // Step 4: Test cache invalidation on delete
        $this->testDeleteInvalidation();

        // Step 5: Show cache statistics
        $this->showCacheStatistics();

        $this->newLine();
        $this->info('✅ Lada Cache testing completed!');

        return self::SUCCESS;
    }

    private function testCachePopulation(): void
    {
        $this->info('📊 Step 1: Testing cache population...');

        // Clear existing cache
        $this->clearLadaCache();

        // Get initial cache key count
        $initialKeys = $this->getLadaCacheKeys();
        $this->line('   Initial cache keys: '.count($initialKeys));

        // Run a query that should be cached
        $expeditions = Expedition::with('project')->limit(3)->get();
        $this->line("   Queried {$expeditions->count()} expeditions");

        // Check cache keys after query
        $afterQueryKeys = $this->getLadaCacheKeys();
        $newKeys = count($afterQueryKeys) - count($initialKeys);

        if ($newKeys > 0) {
            $this->info("   ✅ Cache populated: {$newKeys} new keys created");
            $this->line('   Sample keys:');
            foreach (array_slice($afterQueryKeys, -3) as $key) {
                $this->line('     - '.str_replace($this->cachePrefix, '', $key));
            }
        } else {
            $this->warn('   ⚠️  No new cache keys created - cache might not be working');
        }

        $this->newLine();
    }

    private function testCreateInvalidation(): void
    {
        $this->info('➕ Step 2: Testing cache invalidation on CREATE...');

        // Populate cache first
        Expedition::with('project')->limit(2)->get();
        $beforeKeys = $this->getLadaCacheKeys();
        $this->line('   Cache keys before create: '.count($beforeKeys));

        // Create a new expedition
        $expedition = Expedition::create([
            'title' => 'Test Cache Expedition '.now()->timestamp,
            'description' => 'Testing Lada Cache invalidation',
            'project_id' => 1, // Assuming project ID 1 exists
            'workflow_id' => 1, // Assuming workflow ID 1 exists
        ]);

        $afterKeys = $this->getLadaCacheKeys();
        $keyDifference = count($beforeKeys) - count($afterKeys);

        if ($keyDifference > 0) {
            $this->info("   ✅ Cache invalidated on CREATE: {$keyDifference} keys removed");
        } elseif ($keyDifference < 0) {
            $this->info('   ✅ Cache updated on CREATE: '.abs($keyDifference).' new keys added');
        } else {
            $this->warn('   ⚠️  Cache unchanged after CREATE - might indicate an issue');
        }

        $this->line("   Created expedition ID: {$expedition->id}");
        $this->newLine();

        // Store for later tests
        $this->testExpeditionId = $expedition->id;
    }

    private function testUpdateInvalidation(): void
    {
        $this->info('✏️ Step 3: Testing cache invalidation on UPDATE...');

        // Find the test expedition
        $expedition = Expedition::find($this->testExpeditionId);
        if (! $expedition) {
            $this->error('   ❌ Test expedition not found');

            return;
        }

        // Populate cache
        Expedition::with('project')->where('id', $expedition->id)->first();
        $beforeKeys = $this->getLadaCacheKeys();
        $this->line('   Cache keys before update: '.count($beforeKeys));

        // Update the expedition
        $expedition->update([
            'description' => 'Updated at '.now()->toDateTimeString(),
        ]);

        $afterKeys = $this->getLadaCacheKeys();
        $keyDifference = count($beforeKeys) - count($afterKeys);

        if ($keyDifference > 0) {
            $this->info("   ✅ Cache invalidated on UPDATE: {$keyDifference} keys removed");
        } elseif ($keyDifference < 0) {
            $this->info('   ✅ Cache updated on UPDATE: '.abs($keyDifference).' new keys added');
        } else {
            $this->warn('   ⚠️  Cache unchanged after UPDATE');
        }

        $this->newLine();
    }

    private function testDeleteInvalidation(): void
    {
        $this->info('🗑️ Step 4: Testing cache invalidation on DELETE...');

        $expedition = Expedition::find($this->testExpeditionId);
        if (! $expedition) {
            $this->error('   ❌ Test expedition not found');

            return;
        }

        // Populate cache
        Expedition::with('project')->where('id', $expedition->id)->first();
        $beforeKeys = $this->getLadaCacheKeys();
        $this->line('   Cache keys before delete: '.count($beforeKeys));

        // Delete the expedition
        $expedition->delete();

        $afterKeys = $this->getLadaCacheKeys();
        $keyDifference = count($beforeKeys) - count($afterKeys);

        if ($keyDifference > 0) {
            $this->info("   ✅ Cache invalidated on DELETE: {$keyDifference} keys removed");
        } else {
            $this->warn('   ⚠️  Cache unchanged after DELETE');
        }

        $this->line("   Deleted expedition ID: {$this->testExpeditionId}");
        $this->newLine();
    }

    private function showCacheStatistics(): void
    {
        $this->info('📈 Step 5: Cache Statistics...');

        $totalKeys = $this->getLadaCacheKeys();
        $this->line('   Total Lada Cache keys: '.count($totalKeys));

        // Show cache configuration
        $this->line('   Configuration:');
        $this->line('     - Active: '.(config('lada-cache.active') ? 'Yes' : 'No'));
        $this->line('     - Driver: '.config('lada-cache.driver'));
        $this->line('     - Redis Connection: '.config('lada-cache.redis_connection'));
        $this->line('     - Prefix: '.config('lada-cache.prefix'));
        $this->line('     - Consider Rows: '.(config('lada-cache.consider_rows') ? 'Yes' : 'No'));

        // Show some example keys
        if (count($totalKeys) > 0) {
            $this->line('   Recent cache keys:');
            foreach (array_slice($totalKeys, -5) as $key) {
                $cleanKey = str_replace($this->cachePrefix, '', $key);
                $ttl = $this->getCacheKeyTTL($key);
                $this->line("     - {$cleanKey} (TTL: {$ttl}s)");
            }
        }

        $this->newLine();
    }

    private function getLadaCacheKeys(): array
    {
        try {
            $redis = Redis::connection($this->redisConnection);
            $pattern = $this->cachePrefix.'*';

            return $redis->keys($pattern) ?: [];
        } catch (\Exception $e) {
            $this->warn('   Warning: Could not access Redis keys: '.$e->getMessage());

            return [];
        }
    }

    private function getCacheKeyTTL(string $key): int
    {
        try {
            $redis = Redis::connection($this->redisConnection);

            return $redis->ttl($key);
        } catch (\Exception $e) {
            return -1;
        }
    }

    private function clearLadaCache(): void
    {
        try {
            $redis = Redis::connection($this->redisConnection);
            $keys = $redis->keys($this->cachePrefix.'*');
            if (! empty($keys)) {
                $redis->del($keys);
                $this->line('   Cleared '.count($keys).' existing cache keys');
            }
        } catch (\Exception $e) {
            $this->warn('   Warning: Could not clear cache: '.$e->getMessage());
        }
    }

    private function cleanupTestData(): void
    {
        $this->warn('🧹 Cleaning up test data...');

        $cleaned = Expedition::where('title', 'like', 'Test Cache Expedition%')->delete();
        if ($cleaned > 0) {
            $this->line("   Removed {$cleaned} test expeditions");
        }

        $this->clearLadaCache();
        $this->line('   Cache cleared');
        $this->newLine();
    }

    private int $testExpeditionId;
}
