<?php

namespace Biospex\Providers;

use Biospex\Repositories\Decorators\CacheUserDecorator;
use Illuminate\Support\ServiceProvider;

use Biospex\Models\User;
use Biospex\Models\Group;
use Biospex\Models\Project;
use Biospex\Models\Expedition;
use Biospex\Models\Permission;

use Biospex\Repositories\Contracts\User as UserContract;
use Biospex\Repositories\Contracts\Group as GroupContract;
use Biospex\Repositories\Contracts\Project as ProjectContract;
use Biospex\Repositories\Contracts\Expedition as ExpeditionContract;
use Biospex\Repositories\Contracts\Permission as PermissionContract;
use Biospex\Repositories\Contracts\Subject as SubjectContract;
use Biospex\Repositories\Contracts\Import as ImportContract;
use Biospex\Repositories\Contracts\Header as HeaderContract;
use Biospex\Repositories\Contracts\WorkflowManager as WorkflowManagerContract;
use Biospex\Repositories\Contracts\Actor as ActorContract;
use Biospex\Repositories\Contracts\Download as DownloadContract;
use Biospex\Repositories\Contracts\Invite as InviteContract;
use Biospex\Repositories\Contracts\Property as PropertyContract;
use Biospex\Repositories\Contracts\Meta as MetaContract;
use Biospex\Repositories\Contracts\OcrQueue as OcrQueueContract;
use Biospex\Repositories\Contracts\Transcription as TranscriptionContract;
use Biospex\Repositories\Contracts\OcrCsv as OcrCsvContract;
use Biospex\Repositories\Contracts\UserGridField as UserGridFieldContract;
use Biospex\Repositories\Contracts\ExpeditionStat as ExpeditionStatContract;
use Biospex\Repositories\Contracts\Workflow as WorkflowContract;

use Biospex\Repositories\UserRepository;
use Biospex\Repositories\GroupRepository;
use Biospex\Repositories\ProjectRepository;
use Biospex\Repositories\ExpeditionRepository;
use Biospex\Repositories\PermissionRepository;
use Biospex\Repositories\SubjectRepository;
use Biospex\Repositories\ImportRepository;
use Biospex\Repositories\HeaderRepository;
use Biospex\Repositories\WorkflowManagerRepository;
use Biospex\Repositories\ActorRepository;
use Biospex\Repositories\DownloadRepository;
use Biospex\Repositories\InviteRepository;
use Biospex\Repositories\PropertyRepository;
use Biospex\Repositories\MetaRepository;
use Biospex\Repositories\OcrQueueRepository;
use Biospex\Repositories\TranscriptionRepository;
use Biospex\Repositories\OcrCsvRepository;
use Biospex\Repositories\UserGridFieldRepository;
use Biospex\Repositories\ExpeditionStatRepository;
use Biospex\Repositories\WorkflowRepository;


use Biospex\Repositories\Decorators\CacheGroupDecorator;
use Biospex\Repositories\Decorators\CacheProjectDecorator;
use Biospex\Repositories\Decorators\CacheExpeditionDecorator;

class BiospexServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerRepositories();
    }

    protected function registerRepositories()
    {
        $this->app->singleton(UserContract::class, function () {
            $user = new UserRepository(new User);
            $cache = new CacheUserDecorator($this->app['cache.store'], $user, 'model');

            return $cache;
        });

        $this->app->singleton(GroupContract::class, function () {
            $group = new GroupRepository(new Group);
            $cache = new CacheGroupDecorator($this->app['cache.store'], $group, 'model');

            return $cache;
        });

        $this->app->singleton(PermissionContract::class, function () {
            $permission = new PermissionRepository(new Permission);
            $cache = new CacheGroupDecorator($this->app['cache.store'], $permission, 'model');

            return $cache;
        });

        $this->app->bind(ProjectContract::class, function () {
            $project = new ProjectRepository(new Project);
            $cache = new CacheProjectDecorator($this->app['cache.store'], $project, 'model');

            return $cache;

        });

        $this->app->bind(ExpeditionContract::class, function () {
            $expedition = new ExpeditionRepository(new Expedition);
            $cache = new CacheExpeditionDecorator($this->app['cache.store'], $expedition, 'model');

            return $cache;
        });

        $this->app->bind(PermissionContract::class, PermissionRepository::class);
        $this->app->bind(SubjectContract::class, SubjectRepository::class);
        $this->app->bind(ImportContract::class, ImportRepository::class);
        $this->app->bind(HeaderContract::class, HeaderRepository::class);
        $this->app->bind(WorkflowManagerContract::class, WorkflowManagerRepository::class);
        $this->app->bind(ActorContract::class, ActorRepository::class);
        $this->app->bind(DownloadContract::class, DownloadRepository::class);
        $this->app->bind(InviteContract::class, InviteRepository::class);
        $this->app->bind(PropertyContract::class, PropertyRepository::class);
        $this->app->bind(MetaContract::class, MetaRepository::class);
        $this->app->bind(OcrQueueContract::class, OcrQueueRepository::class);
        $this->app->bind(TranscriptionContract::class, TranscriptionRepository::class);
        $this->app->bind(OcrCsvContract::class, OcrCsvRepository::class);
        $this->app->bind(UserGridFieldContract::class, UserGridFieldRepository::class);
        $this->app->bind(ExpeditionStatContract::class, ExpeditionStatRepository::class);
        $this->app->bind(WorkflowContract::class, WorkflowRepository::class);
    }
}