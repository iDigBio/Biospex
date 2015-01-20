<?php namespace Biospex\ServiceProvider;
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

use Biospex\Repo\Group\GroupRepository;
use Biospex\Repo\Group\CacheGroupDecorator;
use Biospex\Repo\Project\ProjectRepository;
use Biospex\Repo\Project\CacheProjectDecorator;
use Biospex\Repo\Expedition\ExpeditionRepository;
use Biospex\Repo\Expedition\CacheExpeditionDecorator;
use Biospex\Repo\User\UserRepository;
use Biospex\Repo\Session\SentrySession;
use Biospex\Repo\Permission\PermissionRepository;
use Biospex\Repo\Invite\InviteRepository;

use Illuminate\Support\ServiceProvider;
use Biospex\Services\Cache\LaravelCache;

use Group;
use Project;
use Expedition;
use Permission;
use Invite;

class RepoServiceProvider extends ServiceProvider {

    /**
     * Register the binding
     */
    public function register()
    {
        $app = $this->app;

        // Bind the Session Repository with Sentry
        $app->bind('Biospex\Repo\Session\SessionInterface', function($app)
        {
            return new SentrySession(
                $app['sentry']
            );
        });

        // Bind the User Repository with Sentry
        $app->bind('Biospex\Repo\User\UserInterface', function($app)
        {
            return new UserRepository(
                $app['sentry'], new PermissionRepository(new Permission), new InviteRepository(new Invite)
            );
        });

        // Bind the Group Repository with Sentry
		$app->bind('Biospex\Repo\Group\GroupInterface', function($app)
		{
			$group = new GroupRepository(
				new Group, $app['sentry'], new PermissionRepository(new Permission)
			);

			$cache = new CacheGroupDecorator(
				$group, new LaravelCache($app['cache'], 'queries')
			);

			return $cache;
		});

		// $app->bind('Biospex\Repo\Project\ProjectInterface', 'Biospex\Repo\Project\ProjectRepository');
		$app->bind('Biospex\Repo\Project\ProjectInterface', function($app)
		{
			$project = new ProjectRepository(new Project);

			$cache = new CacheProjectDecorator(
				$project, new LaravelCache($app['cache'], 'queries')
			);

			return $cache;

		});

		//$app->bind('Biospex\Repo\Expedition\ExpeditionInterface', 'Biospex\Repo\Expedition\ExpeditionRepository');
		$app->bind('Biospex\Repo\Expedition\ExpeditionInterface', function($app)
		{
			$expedition = new ExpeditionRepository(new Expedition);

			$cache = new CacheExpeditionDecorator(
				$expedition, new LaravelCache($app['cache'], 'queries')
			);

			return $cache;
		});

		$app->bind('Biospex\Repo\Permission\PermissionInterface', 'Biospex\Repo\Permission\PermissionRepository');
        $app->bind('Biospex\Repo\Navigation\NavigationInterface', 'Biospex\Repo\Navigation\NavigationRepository');
        $app->bind('Biospex\Repo\Subject\SubjectInterface', 'Biospex\Repo\Subject\SubjectRepository');
        $app->bind('Biospex\Repo\Import\ImportInterface', 'Biospex\Repo\Import\ImportRepository');
		$app->bind('Biospex\Repo\Header\HeaderInterface', 'Biospex\Repo\Header\HeaderRepository');
        $app->bind('Biospex\Repo\WorkflowManager\WorkflowManagerInterface', 'Biospex\Repo\WorkflowManager\WorkflowManagerRepository');
        $app->bind('Biospex\Repo\Actor\ActorInterface', 'Biospex\Repo\Actor\ActorRepository');
        $app->bind('Biospex\Repo\Download\DownloadInterface', 'Biospex\Repo\Download\DownloadRepository');
        $app->bind('Biospex\Repo\Invite\InviteInterface', 'Biospex\Repo\Invite\InviteRepository');
		$app->bind('Biospex\Repo\Property\PropertyInterface', 'Biospex\Repo\Property\PropertyRepository');
		$app->bind('Biospex\Repo\Meta\MetaInterface', 'Biospex\Repo\Meta\MetaRepository');
		$app->bind('Biospex\Repo\OcrQueue\OcrQueueInterface', 'Biospex\Repo\OcrQueue\OcrQueueRepository');
        $app->bind('Illuminate\Support\Contracts\MessageProviderInterface', 'Illuminate\Support\MessageBag');
    }

}