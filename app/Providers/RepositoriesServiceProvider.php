<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\UserRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\InviteRepository;
use App\Repositories\GroupRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\AuthSession;
use App\Repositories\ExpeditionRepository;
use App\Repositories\Decorators\CacheGroupDecorator;
use App\Repositories\Decorators\CacheProjectDecorator;
use App\Repositories\Decorators\CacheExpeditionDecorator;

use App\Repositories\Contracts\Group as GroupContract;
use App\Models\Group;
use App\Repositories\Contracts\Permission as PermissionContract;
use App\Models\Permission;

use App\Services\Cache\LaravelCache;

use App\Models\Invite;

use App\Models\Project;
use App\Models\Expedition;

class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Register the binding
     */
    public function register()
    {
        $app = $this->app;

        // Bind the Session Repository with Sentry
        $app->bind('App\Repositories\Contracts\Auth', function ($app) {
            return new AuthSession(
                $app['sentry']
            );
        });

        // Bind the User Repository with Sentry
        $app->bind('App\Repositories\Contracts\User', function ($app) {
            return new UserRepository(
                $app['sentry'], new PermissionRepository(new Permission), new InviteRepository(new Invite)
            );
        });

        $this->app->singleton(GroupContract::class, function () {
            $group = new GroupRepository(new Group);

            $cache = new CacheGroupDecorator($group, $this->app['cache.store'], 'model');

            return $cache;
        });

        $this->app->singleton(PermissionContract::class, function () {
            $permission = new PermissionRepository(new Permission);

            $cache = new CacheGroupDecorator($permission, $this->app['cache.store'], 'model');

            return $cache;
        });

        $app->bind('App\Repositories\Contracts\Project', function ($app) {
            $project = new ProjectRepository(new Project);

            $cache = new CacheProjectDecorator(
                $project, new LaravelCache($app['cache.store'], 'queries')
            );

            return $cache;

        });

        $app->bind('App\Repositories\Contracts\Expedition', function ($app) {
            $expedition = new ExpeditionRepository(new Expedition);

            $cache = new CacheExpeditionDecorator(
                $expedition, new LaravelCache($app['cache.store'], 'queries')
            );

            return $cache;
        });

        $app->bind('App\Repositories\Contracts\Permission', 'App\Repositories\PermissionRepository');
        $app->bind('App\Repositories\Contracts\Navigation', 'App\Repositories\NavigationRepository');
        $app->bind('App\Repositories\Contracts\Subject', 'App\Repositories\SubjectRepository');
        $app->bind('App\Repositories\Contracts\Import', 'App\Repositories\ImportRepository');
        $app->bind('App\Repositories\Contracts\Header', 'App\Repositories\HeaderRepository');
        $app->bind('App\Repositories\Contracts\WorkflowManager', 'App\Repositories\WorkflowManagerRepository');
        $app->bind('App\Repositories\Contracts\Actor', 'App\Repositories\ActorRepository');
        $app->bind('App\Repositories\Contracts\Download', 'App\Repositories\DownloadRepository');
        $app->bind('App\Repositories\Contracts\Invite', 'App\Repositories\InviteRepository');
        $app->bind('App\Repositories\Contracts\Property', 'App\Repositories\PropertyRepository');
        $app->bind('App\Repositories\Contracts\Meta', 'App\Repositories\MetaRepository');
        $app->bind('App\Repositories\Contracts\OcrQueue', 'App\Repositories\OcrQueueRepository');
        $app->bind('App\Repositories\Contracts\Transcription', 'App\Repositories\TranscriptionRepository');
        $app->bind('App\Repositories\Contracts\UserGridField', 'App\Repositories\UserGridFieldRepository');

        //$app->bind('Illuminate\Support\Contracts\MessageProvider', 'Illuminate\Support\MessageBag');
    }
}
