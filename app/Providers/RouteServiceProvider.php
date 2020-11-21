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
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

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
        Route::domain(config('config.app_domain'))
            ->namespace($this->namespace)->middleware('web')->group(function () {

                Route::namespace('Front')->group(function () {
                    $this->require_files('routes/front');
                });

                Route::namespace('Auth')->group(function(){
                    $this->require_files('routes/auth');
                });

                Route::namespace('Admin')->prefix('admin')->middleware(['auth', 'verified'])->group(function () {
                    $this->require_files('routes/admin');
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
        $dirPath = base_path().'/'.$dir.'/';
        foreach (new DirectoryIterator($dirPath) as $file) {
            if (! $file->isDot() && ! $file->isDir()) {
                require $dirPath.$file->getFilename();
            }
        }
    }
}