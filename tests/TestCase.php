<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @throws \Exception
     */
    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->clearCache($app); // Added this line.

        return $app;
    }

    /**
     * Clear the configuration cache.
     *
     * @throws \Exception
     */
    protected function clearCache(Application $app): void
    {
        // We don't have a cached config, so continue running the test suite.
        if (! $app->configurationIsCached()) {
            \Artisan::call('auto-cache:clear');

            return;
        }

        $commands = ['clear-compiled', 'cache:clear', 'view:clear', 'config:clear', 'route:clear', 'auto-cache:clear'];
        foreach ($commands as $command) {
            \Illuminate\Support\Facades\Artisan::call($command);
        }
        // Since the config is already loaded in memory at this point,
        // we need to bail so refresh migrations are not ran on our
        // local database.
        throw new \Exception('Your configuration values were cached and have now been cleared. Please rerun the test suite.');
    }
}
