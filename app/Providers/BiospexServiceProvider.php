<?php

namespace App\Providers;

use App\Listeners\RepositoryEventListener;
use Illuminate\Support\ServiceProvider;

use App\Repositories\Contracts\PanoptesTranscriptionContract;
use App\Repositories\Eloquent\PanoptesTranscriptionRepository;
use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Eloquent\ProjectRepository;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Eloquent\ExpeditionRepository;
use App\Repositories\Contracts\SubjectContract;
use App\Repositories\Eloquent\SubjectRepository;
use App\Repositories\Contracts\AmChartContract;
use App\Repositories\Eloquent\AmChartRepository;
use App\Repositories\Contracts\GroupContract;
use App\Repositories\Eloquent\GroupRepository;
use App\Repositories\Contracts\WorkflowManagerContract;
use App\Repositories\Eloquent\WorkflowManagerRepository;
use App\Repositories\Contracts\TranscriptionLocationContract;
use App\Repositories\Eloquent\TranscriptionLocationRepository;
use App\Repositories\Contracts\StateCountyContract;
use App\Repositories\Eloquent\StateCountyRepository;
use App\Repositories\Contracts\ActorContract;
use App\Repositories\Eloquent\ActorRepository;
use App\Repositories\Contracts\UserContract;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Contracts\ExportQueueContract;
use App\Repositories\Eloquent\ExportQueueRepository;
use App\Repositories\Contracts\NfnWorkflowContract;
use App\Repositories\Eloquent\NfnWorkflowRepository;
use App\Repositories\Contracts\DownloadContract;
use App\Repositories\Eloquent\DownloadRepository;
use App\Repositories\Contracts\ImportContract;
use App\Repositories\Eloquent\ImportRepository;


// Replacing eventually
use App\Repositories\Contracts\User as UserContractOld;
use App\Repositories\Contracts\Group as GroupContractOld;
use App\Repositories\Contracts\Project as ProjectContractOld;
use App\Repositories\Contracts\PermissionContract as PermissionContract;
use App\Repositories\Contracts\Subject as SubjectContractOld;
use App\Repositories\Contracts\Import as ImportContractOld;
use App\Repositories\Contracts\Header as HeaderContract;
use App\Repositories\Contracts\WorkflowManagerContract as WorkflowManagerContractOld;
use App\Repositories\Contracts\Actor as ActorContractOld;
use App\Repositories\Contracts\Download as DownloadContractOld;
use App\Repositories\Contracts\Invite as InviteContract;
use App\Repositories\Contracts\PropertyContract as PropertyContract;
use App\Repositories\Contracts\Meta as MetaContract;
use App\Repositories\Contracts\OcrQueueContract as OcrQueueContract;
use App\Repositories\Contracts\NfnTranscriptionContract as NfnTranscriptionContract;
use App\Repositories\Contracts\OcrCsvContract as OcrCsvContract;
use App\Repositories\Contracts\ExpeditionStat as ExpeditionStatContract;
use App\Repositories\Contracts\WorkflowContract as WorkflowContract;
use App\Repositories\Contracts\Faq as FaqContract;
use App\Repositories\Contracts\FaqCategory as FaqCategoryContract;
use App\Repositories\Contracts\AmChart as AmChartContractOld;
use App\Repositories\Contracts\TeamCategoryContract as TeamCategoryContract;
use App\Repositories\Contracts\TeamContract as TeamContract;
use App\Repositories\Contracts\NoticeContract as NoticeContract;
use App\Repositories\Contracts\TranslationContract as TranslationContract;
use App\Repositories\Contracts\ResourceContract as ResourceContract;
use App\Repositories\Contracts\NfnWorkflowContract as NfnWorkflowContractOld;
use App\Repositories\Contracts\NotificationContract as NotificationContract;
use App\Repositories\Contracts\ActorContact as ActorContactContract;

use App\Repositories\UserRepository as UserRepositoryOld;
use App\Repositories\GroupRepository as GroupRepositoryOld;
use App\Repositories\ProjectRepository as ProjectRepositoryOld;
use App\Repositories\PermissionContractRepository;
use App\Repositories\SubjectRepository as SubjectRepositoryOld;
use App\Repositories\ImportRepository as ImportRepositoryOld;
use App\Repositories\HeaderRepository;
use App\Repositories\WorkflowManagerContractRepository as WorkflowManagerRepositoryOld;
use App\Repositories\ActorRepository as ActorRepositoryOld;
use App\Repositories\DownloadRepository as DownloadRepositoryOld;
use App\Repositories\InviteRepository;
use App\Repositories\PropertyContractRepository;
use App\Repositories\MetaRepository;
use App\Repositories\OcrQueueContractRepository;
use App\Repositories\NfnTranscriptionContractRepository;
use App\Repositories\OcrCsvContractRepository;
use App\Repositories\ExpeditionStatRepository;
use App\Repositories\WorkflowContractRepository;
use App\Repositories\FaqRepository;
use App\Repositories\FaqCategoryRepository;
use App\Repositories\AmChartRepository as AmChartRepositoryOld;
use App\Repositories\TeamCategoryContractRepository;
use App\Repositories\TeamContractRepository;
use App\Repositories\NoticeContractRepository;
use App\Repositories\TranslationContractRepository;
use App\Repositories\ResourceContractRepository;
use App\Repositories\NfnWorkflowContractRepository as NfnWorkflowRepositoryOld;
use App\Repositories\NotificationContractRepository;
use App\Repositories\ActorContactRepository;

class BiospexServiceProvider extends ServiceProvider
{
    public function boot()
    {
        require base_path().'/resources/macros/macros.php';

        view()->composer(
            'frontend.layouts.notices', 'App\Http\ViewComposers\NoticesComposer'
        );

        view()->composer(
            'frontend.layouts.partials.authuser', 'App\Http\ViewComposers\NotificationsComposer'
        );

        // Subscribe the registered event listener
        $this->app['events']->subscribe('repository.listener');
    }
    
    public function register()
    {
        // Register the event listener
        $this->app->bind('repository.listener', RepositoryEventListener::class);

        $this->registerRepositories();
    }

    /**
     * Register Repositories
     */
    protected function registerRepositories()
    {
        $this->app->bind(UserContractOld::class, UserRepositoryOld::class);
        $this->app->bind(GroupContractOld::class, GroupRepositoryOld::class);
        $this->app->bind(PermissionContract::class, PermissionContractRepository::class);
        $this->app->bind(ProjectContractOld::class, ProjectRepositoryOld::class);
        $this->app->bind(PermissionContract::class, PermissionContractRepository::class);
        $this->app->bind(SubjectContractOld::class, SubjectRepositoryOld::class);
        $this->app->bind(ImportContractOld::class, ImportRepositoryOld::class);
        $this->app->bind(HeaderContract::class, HeaderRepository::class);
        $this->app->bind(WorkflowManagerContractOld::class, WorkflowManagerRepositoryOld::class);
        $this->app->bind(ActorContractOld::class, ActorRepositoryOld::class);
        $this->app->bind(DownloadContractOld::class, DownloadRepositoryOld::class);
        $this->app->bind(InviteContract::class, InviteRepository::class);
        $this->app->bind(PropertyContract::class, PropertyContractRepository::class);
        $this->app->bind(MetaContract::class, MetaRepository::class);
        $this->app->bind(OcrQueueContract::class, OcrQueueContractRepository::class);
        $this->app->bind(NfnTranscriptionContract::class, NfnTranscriptionContractRepository::class);
        $this->app->bind(OcrCsvContract::class, OcrCsvContractRepository::class);
        $this->app->bind(ExpeditionStatContract::class, ExpeditionStatRepository::class);
        $this->app->bind(WorkflowContract::class, WorkflowContractRepository::class);
        $this->app->bind(FaqContract::class, FaqRepository::class);
        $this->app->bind(FaqCategoryContract::class, FaqCategoryRepository::class);
        $this->app->bind(AmChartContractOld::class, AmChartRepositoryOld::class);
        $this->app->bind(TeamCategoryContract::class, TeamCategoryContractRepository::class);
        $this->app->bind(TeamContract::class, TeamContractRepository::class);
        $this->app->bind(NoticeContract::class, NoticeContractRepository::class);
        $this->app->bind(TranslationContract::class, TranslationContractRepository::class);
        $this->app->bind(ResourceContract::class, ResourceContractRepository::class);
        $this->app->bind(NfnWorkflowContractOld::class, NfnWorkflowRepositoryOld::class);
        $this->app->bind(NotificationContract::class, NotificationContractRepository::class);
        $this->app->bind(ActorContactContract::class, ActorContactRepository::class);

        // New Repository
        $this->app->bind(PanoptesTranscriptionContract::class, PanoptesTranscriptionRepository::class);
        $this->app->bind(ProjectContract::class, ProjectRepository::class);
        $this->app->bind(ExpeditionContract::class, ExpeditionRepository::class);
        $this->app->bind(SubjectContract::class, SubjectRepository::class);
        $this->app->bind(AmChartContract::class, AmChartRepository::class);
        $this->app->bind(GroupContract::class, GroupRepository::class);
        $this->app->bind(WorkflowManagerContract::class, WorkflowManagerRepository::class);
        $this->app->bind(TranscriptionLocationContract::class, TranscriptionLocationRepository::class);
        $this->app->bind(StateCountyContract::class, StateCountyRepository::class);
        $this->app->bind(ActorContract::class, ActorRepository::class);
        $this->app->bind(UserContract::class, UserRepository::class);
        $this->app->bind(ExportQueueContract::class, ExportQueueRepository::class);
        $this->app->bind(NfnWorkflowContract::class, NfnWorkflowRepository::class);
        $this->app->bind(DownloadContract::class, DownloadRepository::class);
        $this->app->bind(ImportContract::class, ImportRepository::class);
    }
}