<?php

namespace App\Providers;

use App\Listeners\RepositoryEventListener;
use Illuminate\Support\ServiceProvider;

use App\Repositories\Contracts\ActorContactContract;
use App\Repositories\Contracts\ActorContract;
use App\Repositories\Contracts\AmChartContract;
use App\Repositories\Contracts\DownloadContract;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\ExpeditionStatContract;
use App\Repositories\Contracts\ExportQueueContract;
use App\Repositories\Contracts\FaqCategoryContract;
use App\Repositories\Contracts\FaqContract;
use App\Repositories\Contracts\GroupContract;
use App\Repositories\Contracts\HeaderContract;
use App\Repositories\Contracts\ImportContract;
use App\Repositories\Contracts\InviteContract;
use App\Repositories\Contracts\MetaContract;
use App\Repositories\Contracts\NfnTranscriptionContract;
use App\Repositories\Contracts\NfnWorkflowContract;
use App\Repositories\Contracts\NoticeContract;
use App\Repositories\Contracts\NotificationContract;
use App\Repositories\Contracts\OcrCsvContract;
use App\Repositories\Contracts\OcrQueueContract;
use App\Repositories\Contracts\PanoptesTranscriptionContract;
use App\Repositories\Contracts\PermissionContract;
use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\PropertyContract;
use App\Repositories\Contracts\ResourceContract;
use App\Repositories\Contracts\StateCountyContract;
use App\Repositories\Contracts\SubjectContract;
use App\Repositories\Contracts\TeamCategoryContract;
use App\Repositories\Contracts\TeamContract;
use App\Repositories\Contracts\TranscriptionLocationContract;
use App\Repositories\Contracts\TranslationContract;
use App\Repositories\Contracts\UserContract;
use App\Repositories\Contracts\WeDigBioDashboardContract;
use App\Repositories\Contracts\WorkflowContract;
use App\Repositories\Contracts\WorkflowManagerContract;

use App\Repositories\Eloquent\ActorContactRepository;
use App\Repositories\Eloquent\ActorRepository;
use App\Repositories\Eloquent\AmChartRepository;
use App\Repositories\Eloquent\DownloadRepository;
use App\Repositories\Eloquent\ExpeditionRepository;
use App\Repositories\Eloquent\ExpeditionStatRepository;
use App\Repositories\Eloquent\ExportQueueRepository;
use App\Repositories\Eloquent\FaqCategoryRepository;
use App\Repositories\Eloquent\FaqRepository;
use App\Repositories\Eloquent\GroupRepository;
use App\Repositories\Eloquent\HeaderRepository;
use App\Repositories\Eloquent\ImportRepository;
use App\Repositories\Eloquent\InviteRepository;
use App\Repositories\Eloquent\MetaRepository;
use App\Repositories\Eloquent\NfnTranscriptionRepository;
use App\Repositories\Eloquent\NfnWorkflowRepository;
use App\Repositories\Eloquent\NoticeRepository;
use App\Repositories\Eloquent\NotificationRepository;
use App\Repositories\Eloquent\OcrCsvRepository;
use App\Repositories\Eloquent\OcrQueueRepository;
use App\Repositories\Eloquent\PanoptesTranscriptionRepository;
use App\Repositories\Eloquent\PermissionRepository;
use App\Repositories\Eloquent\ProjectRepository;
use App\Repositories\Eloquent\PropertyRepository;
use App\Repositories\Eloquent\ResourceRepository;
use App\Repositories\Eloquent\StateCountyRepository;
use App\Repositories\Eloquent\SubjectRepository;
use App\Repositories\Eloquent\TeamCategoryRepository;
use App\Repositories\Eloquent\TeamRepository;
use App\Repositories\Eloquent\TranscriptionLocationRepository;
use App\Repositories\Eloquent\TranslationRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\WeDigBioDashboardRepository;
use App\Repositories\Eloquent\WorkflowManagerRepository;
use App\Repositories\Eloquent\WorkflowRepository;

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
        $this->registerRepositories();

        // Register the event listener
        $this->app->bind('repository.listener', RepositoryEventListener::class);
    }

    /**
     * Register Repositories
     */
    protected function registerRepositories()
    {
        $this->app->bind(ActorContactContract::class, ActorContactRepository::class);
        $this->app->bind(ActorContract::class, ActorRepository::class);
        $this->app->bind(AmChartContract::class, AmChartRepository::class);
        $this->app->bind(DownloadContract::class, DownloadRepository::class);
        $this->app->bind(ExpeditionContract::class, ExpeditionRepository::class);
        $this->app->bind(ExpeditionStatContract::class, ExpeditionStatRepository::class);
        $this->app->bind(ExportQueueContract::class, ExportQueueRepository::class);
        $this->app->bind(FaqCategoryContract::class, FaqCategoryRepository::class);
        $this->app->bind(FaqContract::class, FaqRepository::class);
        $this->app->bind(GroupContract::class, GroupRepository::class);
        $this->app->bind(HeaderContract::class, HeaderRepository::class);
        $this->app->bind(ImportContract::class, ImportRepository::class);
        $this->app->bind(InviteContract::class, ImportRepository::class);
        $this->app->bind(InviteContract::class, InviteRepository::class);
        $this->app->bind(MetaContract::class, MetaRepository::class);
        $this->app->bind(NfnTranscriptionContract::class, NfnTranscriptionRepository::class);
        $this->app->bind(NfnWorkflowContract::class, NfnWorkflowRepository::class);
        $this->app->bind(NoticeContract::class, NoticeRepository::class);
        $this->app->bind(NotificationContract::class, NotificationRepository::class);
        $this->app->bind(OcrCsvContract::class, OcrCsvRepository::class);
        $this->app->bind(OcrQueueContract::class, OcrQueueRepository::class);
        $this->app->bind(PanoptesTranscriptionContract::class, PanoptesTranscriptionRepository::class);
        $this->app->bind(PermissionContract::class, PermissionRepository::class);
        $this->app->bind(ProjectContract::class, ProjectRepository::class);
        $this->app->bind(PropertyContract::class, PropertyRepository::class);
        $this->app->bind(ResourceContract::class, ResourceRepository::class);
        $this->app->bind(StateCountyContract::class, StateCountyRepository::class);
        $this->app->bind(SubjectContract::class, SubjectRepository::class);
        $this->app->bind(TeamCategoryContract::class, TeamCategoryRepository::class);
        $this->app->bind(TeamContract::class, TeamRepository::class);
        $this->app->bind(TranscriptionLocationContract::class, TranscriptionLocationRepository::class);
        $this->app->bind(TranslationContract::class, TranslationRepository::class);
        $this->app->bind(UserContract::class, UserRepository::class);
        $this->app->bind(WeDigBioDashboardContract::class, WeDigBioDashboardRepository::class);
        $this->app->bind(WorkflowContract::class, WorkflowRepository::class);
        $this->app->bind(WorkflowManagerContract::class, WorkflowManagerRepository::class);
    }
}