<?php namespace Biospex\Providers;

use Illuminate\Support\ServiceProvider;

use Biospex\Repositories\UserRepository;
use Biospex\Repositories\PermissionRepository;
use Biospex\Repositories\InviteRepository;
use Biospex\Repositories\GroupRepository;
use Biospex\Repositories\ProjectRepository;
use Biospex\Repositories\AuthSession;
use Biospex\Repositories\ExpeditionRepository;

use Biospex\Repositories\Decorators\CacheGroupDecorator;
use Biospex\Repositories\Decorators\CacheProjectDecorator;
use Biospex\Repositories\Decorators\CacheExpeditionDecorator;


use Biospex\Services\Cache\LaravelCache;


use Biospex\Models\Permission;
use Biospex\Models\Invite;
use Biospex\Models\Group;
use Biospex\Models\Project;
use Biospex\Models\Expedition;

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
        $app->bind('Biospex\Repositories\Contracts\Auth', function($app)
        {
            return new AuthSession(
                $app['sentry']
            );
        });

        // Bind the User Repository with Sentry
        $app->bind('Biospex\Repositories\Contracts\User', function($app)
        {
            return new UserRepository(
                $app['sentry'], new PermissionRepository(new Permission), new InviteRepository(new Invite)
            );
        });

        // Bind the Group Repository with Sentry
		$app->bind('Biospex\Repositories\Contracts\Group', function($app)
		{
			$group = new GroupRepository(
				new Group, $app['sentry'], new PermissionRepository(new Permission)
			);

			$cache = new CacheGroupDecorator(
				$group, new LaravelCache($app['cache.store'], 'queries')
			);

			return $cache;
		});

		$app->bind('Biospex\Repositories\Contracts\Project', function($app)
		{
			$project = new ProjectRepository(new Project);

			$cache = new CacheProjectDecorator(
				$project, new LaravelCache($app['cache.store'], 'queries')
			);

			return $cache;

		});

		$app->bind('Biospex\Repositories\Contracts\Expedition', function($app)
		{
			$expedition = new ExpeditionRepository(new Expedition);

			$cache = new CacheExpeditionDecorator(
				$expedition, new LaravelCache($app['cache.store'], 'queries')
			);

			return $cache;
		});

		$app->bind('Biospex\Repositories\Contracts\Permission', 'Biospex\Repositories\PermissionRepository');
        $app->bind('Biospex\Repositories\Contracts\Navigation', 'Biospex\Repositories\NavigationRepository');
        $app->bind('Biospex\Repositories\Contracts\Subject', 'Biospex\Repositories\SubjectRepository');
        $app->bind('Biospex\Repositories\Contracts\Import', 'Biospex\Repositories\ImportRepository');
		$app->bind('Biospex\Repositories\Contracts\Header', 'Biospex\Repositories\HeaderRepository');
        $app->bind('Biospex\Repositories\Contracts\WorkflowManager', 'Biospex\Repositories\WorkflowManagerRepository');
        $app->bind('Biospex\Repositories\Contracts\Actor', 'Biospex\Repositories\ActorRepository');
        $app->bind('Biospex\Repositories\Contracts\Download', 'Biospex\Repositories\DownloadRepository');
        $app->bind('Biospex\Repositories\Contracts\Invite', 'Biospex\Repositories\InviteRepository');
		$app->bind('Biospex\Repositories\Contracts\Property', 'Biospex\Repositories\PropertyRepository');
		$app->bind('Biospex\Repositories\Contracts\Meta', 'Biospex\Repositories\MetaRepository');
		$app->bind('Biospex\Repositories\Contracts\OcrQueue', 'Biospex\Repositories\OcrQueueRepository');

        //$app->bind('Illuminate\Support\Contracts\MessageProvider', 'Illuminate\Support\MessageBag');
    }

}