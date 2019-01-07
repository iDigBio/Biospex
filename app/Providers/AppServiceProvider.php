<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Schema;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     * creating, created, updating, updated, saving, saved, deleting, deleted, restoring, restored
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        $this->setupBlade();

        if ($this->app->environment() === 'local' && env('DB_LOG'))
        {
            DB::connection('mongodb')->enableQueryLog();
            DB::connection('mongodb')->listen(function ($sql) {
                // $sql is an object with the properties:
                //  sql: The query
                //  bindings: the sql query variables
                //  time: The execution time for the query
                //  connectionName: The name of the connection
                foreach ($sql->bindings as $i => $binding)
                {
                    if ($binding instanceof \DateTime)
                    {
                        $sql->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                    }
                    else
                    {
                        if (is_string($binding))
                        {
                            $sql->bindings[$i] = "'$binding'";
                        }
                    }
                }

                $query = str_replace(array('%', '?'), array('%%', '%s'), $sql->sql);

                $query = vsprintf($query, $sql->bindings);
                Log::info($query);
            });
        }
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

        Blade::if ('apiuser', function () {
            return Auth::guard('apiuser')->check();
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
        $this->app->bind(
            'Illuminate\Contracts\Auth\Registrar',
            'App\Services\Registrar'
        );

        /*
         * Development Providers
         */
        if (\App::environment() !== 'prod')
        {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }
    }
}
