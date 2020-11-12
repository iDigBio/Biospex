<?php
/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
namespace App\Providers;

use DirectoryIterator;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Dingo\Api\Routing\Router;

/**
 * Class RouteServiceProvider
 *
 * @package App\Providers
 */
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
            ->namespace($this->namespace)->middleware('web')->group(function () {

                Route::namespace('Front')->group(function ($router) {
                    $this->require_files('routes/front');
                });

                Route::namespace('Auth')->group(function(){
                    $this->require_files('routes/front/appauth');
                    //base_path('routes/front/appauth/auth.php');
                });

                Route::namespace('Admin')->prefix('admin')->middleware(['auth', 'verified'])->group(function () {
                    $this->require_files('routes/admin');
                    Route::get('/', function (){
                        return redirect()->route('admin.projects.index');
                    });
                });

                Route::prefix('api')->group(function (){
                    Route::namespace('ApiAuth')->group(function () {
                        $this->require_files('routes/front/apiauth');
                    });
                    Route::namespace('Front')->middleware(['auth:apiuser', 'verified:api.verification.notice'])->group(function () {
                        Route::get('dashboard')->uses('ApiController@dashboard')->name('api.get.dashboard');
                    });
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
        $router = app(Router::class);

        $router->version('v0', function ($router) {
            $options = [
                'middleware' => ['api'],
            ];
            $router->group($options, function ($router) {
                $this->require_files('routes/api/v0', $router);
            });
        });

        $router->version('v1', function ($router) {
            $options = [
                'middleware' => ['api'],
            ];

            $router->group($options, function ($router) {
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

        Route::group($defaultOptions, function () {
            $this->require_files('routes/passport');
        });
    }

    /**
     * Load required files.
     *
     * @param $dir
     * @param null $router
     */
    protected function require_files($dir, $router = null)
    {
        $dirPath = base_path().'/'.$dir.'/';
        foreach (new DirectoryIterator($dirPath) as $file) {
            if (! $file->isDot() && ! $file->isDir()) {
                require $dirPath.$file->getFilename();
            }
        }
    }
}