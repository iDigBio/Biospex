<?php

namespace App\Providers;

use DirectoryIterator;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Dingo\Api\Routing\Router as ApiRouter;
use Illuminate\Routing\Router;

class ApiRouteServiceProvider extends ServiceProvider
{

    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers\Api';

    /**
     * @var string
     */
    protected $routes = 'Http/Routes/Api/';

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
                'namespace' => $this->namespace . '\v1'
            ], function ($api)
            {
                $dir = app_path($this->routes . '/v1');
                $this->require_files($dir, $api);
            });
        });
    }

    /**
     * Load required files.
     *
     * @param $dir
     * @param $api
     */
    protected function require_files($dir, $api)
    {
        foreach (new DirectoryIterator($dir) as $file)
        {
            if (!$file->isDot() && !$file->isDir())
            {
                require $dir . '/' . $file->getFilename();
            }
        }
    }
}

