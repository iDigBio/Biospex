<?php namespace Biospex\ServiceProvider;
/**
 * BiospexServiceProvider.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class BiospexServiceProvider extends ServiceProvider {

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
        $repoProvider = new RepoServiceProvider($this->app);
        $repoProvider->register();

        $formServiceProvider = new FormServiceProvider($this->app);
        $formServiceProvider->register();

        $composerServiceProvider = new ComposerServiceProvider($this->app);
        $composerServiceProvider->register();

		$this->app->booting(function()
		{
			$loader = AliasLoader::getInstance();
			$loader->alias('Helper', 'Biospex\Helpers\Helper');
		});
	}

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

}