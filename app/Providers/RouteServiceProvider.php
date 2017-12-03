<?php

namespace App\Providers;

use DirectoryIterator;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{

    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();

        $this->mapApiRoutes();

        $this->mapPassportRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::domain(config('config.app_domain'))
            ->namespace($this->namespace)
            ->middleware('web')
            ->group(function ($router) {

                $router->namespace('Frontend')->group(function ($router) {
                    $this->require_files('routes/web', $router);

                    $router->middleware('auth')->group(function ($router) {
                        $this->require_files('routes/auth', $router);
                    });
                });

                $router->namespace('Auth')->group(base_path('routes/web/appauth/auth.php'));
                $router->namespace('ApiAuth')->prefix('api')->group(base_path('routes/web/apiauth/auth.php'));

                $router->prefix('admin')
                    ->middleware(['auth', 'admin'])
                    ->namespace('Backend')
                    ->group(function ($router) {
                        $this->require_files('routes/admin', $router);
                    });
            });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        $router = app('Dingo\Api\Routing\Router');

        $router->version('v0', function ($router) {
            $options = [
                'namespace' => 'App\Http\Controllers\Api\V0',
                'middleware' => ['api']
            ];
            $router->group($options, function ($router) {
                require base_path('routes/api/v0/wedigbiodashboard.php');
            });
        });

        $router->version('v1', function ($router) {
            $options = [
                'namespace' => 'App\Http\Controllers\Api\V1',
                'middleware' => ['api']
            ];

            $router->group($options, function ($router) {
                require base_path('routes/api/panoptes-pusher.php');
                $this->require_files('routes/api', $router);
                $router->group(['middleware' => 'client'], function ($router) {
                    $this->require_files('routes/api/v1', $router);
                });
            });
        });

    }

    /**
     * Map Passport routes.
     */
    protected function mapPassportRoutes()
    {
        $defaultOptions = [
            'prefix'    => 'oauth',
            'namespace' => '\Laravel\Passport\Http\Controllers',
        ];

        Route::group($defaultOptions, function ($router) {
            $this->require_files('routes/passport', $router);
        });
    }

    /**
     * Load required files.
     *
     * @param $dir
     * @param $router
     */
    protected function require_files($dir, $router)
    {
        $dirPath = base_path() . '/' . $dir . '/';
        foreach (new DirectoryIterator($dirPath) as $file)
        {
            if ( ! $file->isDot() && ! $file->isDir())
            {
                require $dirPath . $file->getFilename();
            }
        }
    }
}