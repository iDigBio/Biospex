<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Dingo\Api\Routing\Router as ApiRouter;
use Illuminate\Routing\Router;

class ApiRouteServiceProvider extends ServiceProvider
{

    /** This is not quite interesting since it's like laravel ...
     * @param Router $router
     */
    public function boot(Router $router)
    {
        parent::boot($router);
    }


    /**
     * Define the API version v1 Application Routes
     *
     * @param ApiRouter $api
     */
    public function map(ApiRouter $api)
    {
        $api->version('v1', function ($api)
        {
            $api->group([
                'middleware' => 'api',
                'namespace' => 'App\Http\Controllers\Api\v1'
            ], function ($api)
            {
                require app_path('Http/api_v1_routes.php');# here we load the api v1 routes
            });
        });
    }
}

