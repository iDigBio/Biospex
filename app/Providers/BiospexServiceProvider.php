<?php

namespace App\Providers;

use App\Services\Toastr\Toastr;
use Illuminate\Support\ServiceProvider;

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
use App\Repositories\Contracts\Faq as FaqContract;
use App\Repositories\Contracts\FaqCategory as FaqCategoryContract;

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
use App\Repositories\FaqRepository;
use App\Repositories\FaqCategoryRepository;


class BiospexServiceProvider extends ServiceProvider
{
    public function boot()
    {
        require base_path().'/resources/macros/macros.php';
    }
    
    public function register()
    {
        $this->app->register(ApiRouteServiceProvider::class);
        $this->registerRepositories();
    }

    protected function registerRepositories()
    {
        $this->app->bind(UserContract::class, UserRepository::class);
        $this->app->bind(GroupContract::class, GroupRepository::class);
        $this->app->bind(PermissionContract::class, PermissionRepository::class);
        $this->app->bind(ProjectContract::class, ProjectRepository::class);
        $this->app->bind(ExpeditionContract::class, ExpeditionRepository::class);
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
        $this->app->bind(FaqContract::class, FaqRepository::class);
        $this->app->bind(FaqCategoryContract::class, FaqCategoryRepository::class);
    }
}