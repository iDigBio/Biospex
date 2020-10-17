<?php
/**
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

use App\Services\FlashService;
use Illuminate\Support\ServiceProvider;

use App\Repositories\Interfaces\User;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Interfaces\RapidRecord;
use App\Repositories\MongoDb\RapidRecordRepository;
use App\Repositories\Interfaces\RapidUpdate;
use App\Repositories\Eloquent\RapidUpdateRepository;
use App\Repositories\Interfaces\ExportForm;
use App\Repositories\Eloquent\ExportFormRepository;

class BiospexServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->setViewComposers();
    }
    
    public function register()
    {
        $this->registerRepositories();
        $this->registerFacades();
    }

    /**
     * Set up view composers
     */
    public function setViewComposers()
    {

    }

    /**
     * Register Repositories
     */
    protected function registerRepositories()
    {
        $this->app->bind(User::class, UserRepository::class);
        $this->app->bind(RapidRecord::class, RapidRecordRepository::class);
        $this->app->bind(RapidUpdate::class, RapidUpdateRepository::class);
        $this->app->bind(ExportForm::class, ExportFormRepository::class);
    }

    /**
     * Registers custom facades
     */
    public function registerFacades()
    {
        $this->app->singleton('flashfacade', function ()
        {
            return new FlashService();
        });
    }
}