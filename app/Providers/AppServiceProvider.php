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

namespace App\Providers;

use Aws\Lambda\LambdaClient;
use Aws\S3\S3Client;
use Aws\Sfn\SfnClient;
use Aws\Sqs\SqsClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;
use Schema;

/**
 * Class AppServiceProvider
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     * creating, created, updating, updated, saving, saved, deleting, deleted, restoring, restored
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Redis::enableEvents();
        Paginator::useBootstrap();

        $this->setupBlade();

        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventAccessingMissingAttributes(! $this->app->isProduction());
    }

    /**
     * Set up blade extension.
     */
    protected function setupBlade()
    {
        $blade = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();

        $blade->extend(function ($value) {
            return preg_replace('/(\s*)@(break|continue)(\s*)/', '$1<?php $2; ?>$3', $value);
        });
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        $awsConfig = [
            'version' => 'latest',
            'region' => config('services.aws.region', 'us-east-2'),
        ];

        $key = config('services.aws.credentials.key');
        $secret = config('services.aws.credentials.secret');

        // If keys are in .env, use them. If not, the SDK automatically
        // uses the IAM Role (for EC2/Production).
        if (! empty($key) && ! empty($secret)) {
            $awsConfig['credentials'] = [
                'key' => $key,
                'secret' => $secret,
            ];
        }

        // Register AWS Clients as singletons
        $this->app->singleton(SqsClient::class, fn () => new SqsClient($awsConfig));
        $this->app->singleton(S3Client::class, fn () => new S3Client($awsConfig));
        $this->app->singleton(SfnClient::class, fn () => new SfnClient($awsConfig));
        $this->app->singleton(LambdaClient::class, fn () => new LambdaClient($awsConfig));
    }
}
