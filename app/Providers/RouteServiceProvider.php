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

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * Class RouteServiceProvider
 *
 * @package App\Providers
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/projects';

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
        Route::domain(config('config.api.domain'))->group(function () {
            Route::middleware(['web'])->group(base_path('routes/api/index.php'));

            Route::middleware([
                'api',
                //'auth:sanctum',
            ])->group(function () {
                $this->require_files('routes/api/v0');
            });

            Route::prefix('v1')->middleware([
                'api',
                'auth:sanctum',
                'ability:panoptes-pusher:read,panoptes-pusher:create,wedigbio-dashboard:read,lambda:update'
            ])->group(function () {
                $this->require_files('routes/api/v1');
            });
        });
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
        Route::domain(config('config.app_domain'))->middleware('web')->group(function () {
            $this->require_files('routes/front');
            $this->require_files('routes/front/appauth');

            Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {
                $this->require_files('routes/admin');
                Route::get('/', function () {
                    return \Redirect::route('admin.projects.index');
                });
            });
        });
    }

    /**
     * Load required files.
     *
     * @param $dir
     */
    protected function require_files($dir)
    {
        $files = \File::files(base_path($dir));
        foreach ($files as $file) {
            require $file->getPathname();
        }
    }
}