<?php

namespace App\Providers;

use App\Repositories\Decorators\CacheUserDecorator;
use Illuminate\Support\ServiceProvider;

use App\Models\User;
use App\Models\Group;
use App\Models\Project;
use App\Models\Expedition;
use App\Models\Permission;

use App\Repositories\Contracts\User as UserContract;
use App\Repositories\Contracts\Group as GroupContract;
use App\Repositories\Contracts\Project as ProjectContract;
use App\Repositories\Contracts\Expedition as ExpeditionContract;
use App\Repositories\Contracts\Permission as PermissionContract;
use App\Repositories\Contracts\Subject as SubjectContract;
use App\Repositories\Contracts\Import as ImportContract;
use App\Repositories\Contracts\Header as HeaderContract;
use App\Repositories\Contracts\WorkflowManager as WorkflowManagerContract;
use App\Repositories\Contracts\Actor as ActorContract;
use App\Repositories\Contracts\Download as DownloadContract;
use App\Repositories\Contracts\Invite as InviteContract;
use App\Repositories\Contracts\Property as PropertyContract;
use App\Repositories\Contracts\Meta as MetaContract;
use App\Repositories\Contracts\OcrQueue as OcrQueueContract;
use App\Repositories\Contracts\Transcription as TranscriptionContract;
use App\Repositories\Contracts\OcrCsv as OcrCsvContract;
use App\Repositories\Contracts\ExpeditionStat as ExpeditionStatContract;
use App\Repositories\Contracts\Workflow as WorkflowContract;

use App\Repositories\UserRepository;
use App\Repositories\GroupRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\ExpeditionRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\ImportRepository;
use App\Repositories\HeaderRepository;
use App\Repositories\WorkflowManagerRepository;
use App\Repositories\ActorRepository;
use App\Repositories\DownloadRepository;
use App\Repositories\InviteRepository;
use App\Repositories\PropertyRepository;
use App\Repositories\MetaRepository;
use App\Repositories\OcrQueueRepository;
use App\Repositories\TranscriptionRepository;
use App\Repositories\OcrCsvRepository;
use App\Repositories\ExpeditionStatRepository;
use App\Repositories\WorkflowRepository;


use App\Repositories\Decorators\CacheGroupDecorator;
use App\Repositories\Decorators\CacheProjectDecorator;
use App\Repositories\Decorators\CacheExpeditionDecorator;

class BiospexServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(ApiRouteServiceProvider::class);
        
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
        $this->app->bind(ExpeditionStatContract::class, ExpeditionStatRepository::class);
        $this->app->bind(WorkflowContract::class, WorkflowRepository::class);
    }
}