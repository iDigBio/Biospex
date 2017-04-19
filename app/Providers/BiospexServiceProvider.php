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


// Replacing eventually
use App\Repositories\Contracts\User as UserContractOld;
use App\Repositories\Contracts\Group as GroupContractOld;
use App\Repositories\Contracts\Project as ProjectContractOld;
use App\Repositories\Contracts\Expedition as ExpeditionContractOld;
use App\Repositories\Contracts\Permission as PermissionContract;
use App\Repositories\Contracts\Subject as SubjectContractOld;
use App\Repositories\Contracts\Import as ImportContract;
use App\Repositories\Contracts\Header as HeaderContract;
use App\Repositories\Contracts\WorkflowManager as WorkflowManagerContractOld;
use App\Repositories\Contracts\Actor as ActorContractOld;
use App\Repositories\Contracts\Download as DownloadContract;
use App\Repositories\Contracts\Invite as InviteContract;
use App\Repositories\Contracts\Property as PropertyContract;
use App\Repositories\Contracts\Meta as MetaContract;
use App\Repositories\Contracts\OcrQueue as OcrQueueContract;
use App\Repositories\Contracts\NfnTranscription as NfnTranscriptionContract;
use App\Repositories\Contracts\OcrCsv as OcrCsvContract;
use App\Repositories\Contracts\ExpeditionStat as ExpeditionStatContract;
use App\Repositories\Contracts\Workflow as WorkflowContract;
use App\Repositories\Contracts\Faq as FaqContract;
use App\Repositories\Contracts\FaqCategory as FaqCategoryContract;
use App\Repositories\Contracts\AmChart as AmChartContractOld;
use App\Repositories\Contracts\TeamCategory as TeamCategoryContract;
use App\Repositories\Contracts\Team as TeamContract;
use App\Repositories\Contracts\Notice as NoticeContract;
use App\Repositories\Contracts\Translation as TranslationContract;
use App\Repositories\Contracts\Resource as ResourceContract;
use App\Repositories\Contracts\NfnClassification as NfnClassificationContract;
use App\Repositories\Contracts\NfnWorkflow as NfnWorkflowContract;
use App\Repositories\Contracts\Notification as NotificationContract;
use App\Repositories\Contracts\ActorContact as ActorContactContract;

use App\Repositories\UserRepository as UserRepositoryOld;
use App\Repositories\GroupRepository as GroupRepositoryOld;
use App\Repositories\ProjectRepository as ProjectRepositoryOld;
use App\Repositories\ExpeditionRepository as ExpeditionRepositoryOld;
use App\Repositories\PermissionRepository;
use App\Repositories\SubjectRepository as SubjectRepositoryOld;
use App\Repositories\ImportRepository;
use App\Repositories\HeaderRepository;
use App\Repositories\WorkflowManagerRepository as WorkflowManagerRepositoryOld;
use App\Repositories\ActorRepository as ActorRepositoryOld;
use App\Repositories\DownloadRepository;
use App\Repositories\InviteRepository;
use App\Repositories\PropertyRepository;
use App\Repositories\MetaRepository;
use App\Repositories\OcrQueueRepository;
use App\Repositories\NfnTranscriptionRepository;
use App\Repositories\OcrCsvRepository;
use App\Repositories\ExpeditionStatRepository;
use App\Repositories\WorkflowRepository;
use App\Repositories\FaqRepository;
use App\Repositories\FaqCategoryRepository;
use App\Repositories\AmChartRepository as AmChartRepositoryOld;
use App\Repositories\TeamCategoryRepository;
use App\Repositories\TeamRepository;
use App\Repositories\NoticeRepository;
use App\Repositories\TranslationRepository;
use App\Repositories\ResourceRepository;
use App\Repositories\NfnClassificationRepository;
use App\Repositories\NfnWorkflowRepository;
use App\Repositories\NotificationRepository;
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
        $this->app->bind(PermissionContract::class, PermissionRepository::class);
        $this->app->bind(ProjectContractOld::class, ProjectRepositoryOld::class);
        $this->app->bind(ExpeditionContractOld::class, ExpeditionRepositoryOld::class);
        $this->app->bind(PermissionContract::class, PermissionRepository::class);
        $this->app->bind(SubjectContractOld::class, SubjectRepositoryOld::class);
        $this->app->bind(ImportContract::class, ImportRepository::class);
        $this->app->bind(HeaderContract::class, HeaderRepository::class);
        $this->app->bind(WorkflowManagerContractOld::class, WorkflowManagerRepositoryOld::class);
        $this->app->bind(ActorContractOld::class, ActorRepositoryOld::class);
        $this->app->bind(DownloadContract::class, DownloadRepository::class);
        $this->app->bind(InviteContract::class, InviteRepository::class);
        $this->app->bind(PropertyContract::class, PropertyRepository::class);
        $this->app->bind(MetaContract::class, MetaRepository::class);
        $this->app->bind(OcrQueueContract::class, OcrQueueRepository::class);
        $this->app->bind(NfnTranscriptionContract::class, NfnTranscriptionRepository::class);
        $this->app->bind(OcrCsvContract::class, OcrCsvRepository::class);
        $this->app->bind(ExpeditionStatContract::class, ExpeditionStatRepository::class);
        $this->app->bind(WorkflowContract::class, WorkflowRepository::class);
        $this->app->bind(FaqContract::class, FaqRepository::class);
        $this->app->bind(FaqCategoryContract::class, FaqCategoryRepository::class);
        $this->app->bind(AmChartContractOld::class, AmChartRepositoryOld::class);
        $this->app->bind(TeamCategoryContract::class, TeamCategoryRepository::class);
        $this->app->bind(TeamContract::class, TeamRepository::class);
        $this->app->bind(NoticeContract::class, NoticeRepository::class);
        $this->app->bind(TranslationContract::class, TranslationRepository::class);
        $this->app->bind(ResourceContract::class, ResourceRepository::class);
        $this->app->bind(NfnClassificationContract::class, NfnClassificationRepository::class);
        $this->app->bind(NfnWorkflowContract::class, NfnWorkflowRepository::class);
        $this->app->bind(NotificationContract::class, NotificationRepository::class);
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
    }
}