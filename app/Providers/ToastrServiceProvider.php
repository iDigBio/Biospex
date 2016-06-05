<?php

namespace App\Providers;

use App\Services\Toastr\Toastr;
use Illuminate\Support\ServiceProvider;

class ToastrServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('toastr', function ($app)
        {
            return new Toastr($app['session'], $app['config']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['toastr'];
    }
}