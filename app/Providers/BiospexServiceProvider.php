<?php

namespace App\Providers;

use App\Observers\ExportQueueObserver;
use App\Observers\PanoptesTranscriptionObserver;
use Illuminate\Support\ServiceProvider;

use App\Interfaces\ActorContact;
use App\Interfaces\Actor;
use App\Interfaces\AmChart;
use App\Interfaces\ApiUser;
use App\Interfaces\Download;
use App\Interfaces\Expedition;
use App\Interfaces\ExpeditionStat;
use App\Interfaces\ExportQueue;
use App\Interfaces\FaqCategory;
use App\Interfaces\Faq;
use App\Interfaces\Group;
use App\Interfaces\Header;
use App\Interfaces\Import;
use App\Interfaces\Invite;
use App\Interfaces\Meta;
use App\Interfaces\NfnTranscription;
use App\Interfaces\NfnWorkflow;
use App\Interfaces\Notice;
use App\Interfaces\OcrCsv;
use App\Interfaces\OcrQueue;
use App\Interfaces\PanoptesTranscription;
use App\Interfaces\Permission;
use App\Interfaces\Project;
use App\Interfaces\Property;
use App\Interfaces\Resource;
use App\Interfaces\State;
use App\Interfaces\Subject;
use App\Interfaces\TeamCategory;
use App\Interfaces\Team;
use App\Interfaces\TranscriptionLocation;
use App\Interfaces\Translation;
use App\Interfaces\User;
use App\Interfaces\WeDigBioDashboard;
use App\Interfaces\Workflow;
use App\Interfaces\WorkflowManager;

use App\Repositories\ActorContactRepository;
use App\Repositories\ActorRepository;
use App\Repositories\AmChartRepository;
use App\Repositories\ApiUserRepository;
use App\Repositories\DownloadRepository;
use App\Repositories\ExpeditionRepository;
use App\Repositories\ExpeditionStatRepository;
use App\Repositories\ExportQueueRepository;
use App\Repositories\FaqCategoryRepository;
use App\Repositories\FaqRepository;
use App\Repositories\GroupRepository;
use App\Repositories\HeaderRepository;
use App\Repositories\ImportRepository;
use App\Repositories\InviteRepository;
use App\Repositories\MetaRepository;
use App\Repositories\NfnTranscriptionRepository;
use App\Repositories\NfnWorkflowRepository;
use App\Repositories\NoticeRepository;
use App\Repositories\OcrCsvRepository;
use App\Repositories\OcrQueueRepository;
use App\Repositories\PanoptesTranscriptionRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\PropertyRepository;
use App\Repositories\ResourceRepository;
use App\Repositories\StateCountyRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\TeamCategoryRepository;
use App\Repositories\TeamRepository;
use App\Repositories\TranscriptionLocationRepository;
use App\Repositories\TranslationRepository;
use App\Repositories\UserRepository;
use App\Repositories\WeDigBioDashboardRepository;
use App\Repositories\WorkflowManagerRepository;
use App\Repositories\WorkflowRepository;

class BiospexServiceProvider extends ServiceProvider
{
    public function boot()
    {
        require app_path('Macros/macros.php');

        $this->setViewComposers();
        $this->setObservers();
    }
    
    public function register()
    {
        $this->registerRepositories();
        $this->registerFacades();
    }

    /**
     * Set up view composers
     */
    public function setViewComposers()
    {
        view()->composer(
            'frontend.layouts.notices', 'App\Http\ViewComposers\NoticesComposer'
        );

        view()->composer(
            'frontend.layouts.partials.process-modal', 'App\Http\ViewComposers\EchoVarsComposer'
        );
    }

    /**
     * Set observers
     */
    public function setObservers()
    {
        //\App\Models\ExportQueue::observe(ExportQueueObserver::class);
        \App\Models\PanoptesTranscription::observe(PanoptesTranscriptionObserver::class);
    }

    /**
     * Register Repositories
     */
    protected function registerRepositories()
    {
        $this->app->bind(ActorContact::class, ActorContactRepository::class);
        $this->app->bind(Actor::class, ActorRepository::class);
        $this->app->bind(AmChart::class, AmChartRepository::class);
        $this->app->bind(ApiUser::class, ApiUserRepository::class);
        $this->app->bind(Download::class, DownloadRepository::class);
        $this->app->bind(Expedition::class, ExpeditionRepository::class);
        $this->app->bind(ExpeditionStat::class, ExpeditionStatRepository::class);
        $this->app->bind(ExportQueue::class, ExportQueueRepository::class);
        $this->app->bind(FaqCategory::class, FaqCategoryRepository::class);
        $this->app->bind(Faq::class, FaqRepository::class);
        $this->app->bind(Group::class, GroupRepository::class);
        $this->app->bind(Header::class, HeaderRepository::class);
        $this->app->bind(Import::class, ImportRepository::class);
        $this->app->bind(Invite::class, ImportRepository::class);
        $this->app->bind(Invite::class, InviteRepository::class);
        $this->app->bind(Meta::class, MetaRepository::class);
        $this->app->bind(NfnTranscription::class, NfnTranscriptionRepository::class);
        $this->app->bind(NfnWorkflow::class, NfnWorkflowRepository::class);
        $this->app->bind(Notice::class, NoticeRepository::class);
        $this->app->bind(OcrCsv::class, OcrCsvRepository::class);
        $this->app->bind(OcrQueue::class, OcrQueueRepository::class);
        $this->app->bind(PanoptesTranscription::class, PanoptesTranscriptionRepository::class);
        $this->app->bind(Permission::class, PermissionRepository::class);
        $this->app->bind(Project::class, ProjectRepository::class);
        $this->app->bind(Property::class, PropertyRepository::class);
        $this->app->bind(Resource::class, ResourceRepository::class);
        $this->app->bind(State::class, StateCountyRepository::class);
        $this->app->bind(Subject::class, SubjectRepository::class);
        $this->app->bind(TeamCategory::class, TeamCategoryRepository::class);
        $this->app->bind(Team::class, TeamRepository::class);
        $this->app->bind(TranscriptionLocation::class, TranscriptionLocationRepository::class);
        $this->app->bind(Translation::class, TranslationRepository::class);
        $this->app->bind(User::class, UserRepository::class);
        $this->app->bind(WeDigBioDashboard::class, WeDigBioDashboardRepository::class);
        $this->app->bind(Workflow::class, WorkflowRepository::class);
        $this->app->bind(WorkflowManager::class, WorkflowManagerRepository::class);
    }

    /**
     * Registers custom facades
     */
    public function registerFacades()
    {
        $this->app->singleton('flash', function ()
        {
            return new \App\Services\Facades\Flash();
        });

        $this->app->singleton('datehelper', function(){
            return new \App\Services\Facades\DateHelper();
        });
    }
}