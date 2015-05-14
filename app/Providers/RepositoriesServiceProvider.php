<?php namespace Biospex\Providers;
/**
 * RepoServiceProvider.php
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

use Biospex\Repositories\UserRepository;
use Biospex\Repositories\PermissionRepository;
use Biospex\Repositories\InviteRepository;
use Biospex\Repositories\GroupRepository;
use Biospex\Repositories\ProjectRepository;
use Biospex\Repositories\SentrySession;

use Biospex\Repositories\Decorators\CacheGroupDecorator;
use Biospex\Repositories\Decorators\CacheProjectDecorator;


use Biospex\Services\Cache\LaravelCache;


use Biospex\Models\Permission;
use Biospex\Models\Invite;
use Biospex\Models\Group;
use Biospex\Models\Project;

/*
use Biospex\Repositories\GroupRepository;
use Biospex\Repo\Group\CacheGroupDecorator;
use Biospex\Repo\Project\ProjectRepository;
use Biospex\Repo\Project\CacheProjectDecorator;
use Biospex\Repo\Expedition\ExpeditionRepository;
use Biospex\Repo\Expedition\CacheExpeditionDecorator;

use Biospex\Repo\Session\SentrySession;
use Biospex\Repo\Permission\PermissionRepository;
use Biospex\Repo\Invite\InviteRepository;


use Biospex\Services\Cache\LaravelCache;

use Group;
use Project;
use Expedition;

use Invite;
*/

class RepositoriesServiceProvider extends ServiceProvider {

    /**
     * Register the binding
     */
    public function register()
    {
        $app = $this->app;

        // Bind the Session Repository with Sentry
        $app->bind('Biospex\Repositories\Contracts\SessionInterface', function($app)
        {
            return new SentrySession(
                $app['sentry']
            );
        });

        // Bind the User Repository with Sentry
        $app->bind('Biospex\Repositories\Contracts\UserInterface', function($app)
        {
            return new UserRepository(
                $app['sentry'], new PermissionRepository(new Permission), new InviteRepository(new Invite)
            );
        });

        // Bind the Group Repository with Sentry
		$app->bind('Biospex\Repositories\Contracts\GroupInterface', function($app)
		{
			$group = new GroupRepository(
				new Group, $app['sentry'], new PermissionRepository(new Permission)
			);

			$cache = new CacheGroupDecorator(
				$group, new LaravelCache($app['cache.store'], 'queries')
			);

			return $cache;
		});

		// $app->bind('Biospex\Repositories\Contracts\ProjectInterface', 'Biospex\Repo\Project\ProjectRepository');
		$app->bind('Biospex\Repositories\Contracts\ProjectInterface', function($app)
		{
			$project = new ProjectRepository(new Project);

			$cache = new CacheProjectDecorator(
				$project, new LaravelCache($app['cache.store'], 'queries')
			);

			return $cache;

		});

		//$app->bind('Biospex\Repositories\Contracts\ExpeditionInterface', 'Biospex\Repo\Expedition\ExpeditionRepository');
		$app->bind('Biospex\Repositories\Contracts\ExpeditionInterface', function($app)
		{
			$expedition = new ExpeditionRepository(new Expedition);

			$cache = new CacheExpeditionDecorator(
				$expedition, new LaravelCache($app['cache.store'], 'queries')
			);

			return $cache;
		});

		$app->bind('Biospex\Repositories\Contracts\PermissionInterface', 'Biospex\Repositories\PermissionRepository');
        $app->bind('Biospex\Repositories\Contracts\NavigationInterface', 'Biospex\Repositories\NavigationRepository');
        $app->bind('Biospex\Repositories\Contracts\SubjectInterface', 'Biospex\Repositories\SubjectRepository');
        $app->bind('Biospex\Repositories\Contracts\ImportInterface', 'Biospex\Repositories\ImportRepository');
		$app->bind('Biospex\Repositories\Contracts\HeaderInterface', 'Biospex\Repositories\HeaderRepository');
        $app->bind('Biospex\Repositories\Contracts\WorkflowManagerInterface', 'Biospex\Repositories\WorkflowManagerRepository');
        $app->bind('Biospex\Repositories\Contracts\ActorInterface', 'Biospex\Repositories\ActorRepository');
        $app->bind('Biospex\Repositories\Contracts\DownloadInterface', 'Biospex\Repositories\DownloadRepository');
        $app->bind('Biospex\Repositories\Contracts\InviteInterface', 'Biospex\Repositories\InviteRepository');
		$app->bind('Biospex\Repositories\Contracts\PropertyInterface', 'Biospex\Repositories\PropertyRepository');
		$app->bind('Biospex\Repositories\Contracts\MetaInterface', 'Biospex\Repositories\MetaRepository');
		$app->bind('Biospex\Repositories\Contracts\OcrQueueInterface', 'Biospex\Repositories\OcrQueueRepository');

        //$app->bind('Illuminate\Support\Contracts\MessageProviderInterface', 'Illuminate\Support\MessageBag');
    }

}