<?php

namespace App\Http\Controllers\Front;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Jobs\DeleteProject;
use App\Jobs\OcrCreateJob;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\Subject;
use App\Repositories\Interfaces\User;
use App\Http\Requests\ProjectFormRequest;
use App\Services\File\FileService;
use App\Services\Model\CommonVariables;
use App\Services\MongoDbService;
use JavaScript;

class ProjectsController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Group
     */
    private $groupContract;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * @var \App\Services\Model\CommonVariables
     */
    private $commonVariables;

    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * @var \App\Repositories\Interfaces\Subject
     */
    private $subjectContract;

    /**
     * @var \App\Services\File\FileService
     */
    private $fileService;

    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * ProjectsController constructor.
     *
     * @param \App\Repositories\Interfaces\Group $groupContract
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Repositories\Interfaces\Subject $subjectContract
     * @param \App\Services\File\FileService $fileService
     * @param \App\Services\MongoDbService $mongoDbService
     * @param \App\Services\Model\CommonVariables $commonVariables
     */
    public function __construct(
        Group $groupContract,
        Project $projectContract,
        Expedition $expeditionContract,
        Subject $subjectContract,
        FileService $fileService,
        MongoDbService $mongoDbService,
        CommonVariables $commonVariables
    ) {
        $this->groupContract = $groupContract;
        $this->commonVariables = $commonVariables;
        $this->projectContract = $projectContract;
        $this->expeditionContract = $expeditionContract;
        $this->subjectContract = $subjectContract;
        $this->fileService = $fileService;
        $this->mongoDbService = $mongoDbService;
    }

    /**
     * Public Projects page.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param null $sort
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Project $projectContract, $sort = null)
    {
        $projects = $projectContract->getPublicProjectIndex($sort);

        return request()->ajax() ?
            view('front.project.partials.project', compact('projects')) :
            view('front.project.index', compact('projects'));
    }

    /**
     * Show public project page.
     *
     * @param $slug
     * @param Project $projectContract
     * @return \Illuminate\View\View
     */
    public function project(Project $projectContract, $slug)
    {
        $project = $projectContract->getProjectPageBySlug($slug);

        return view('front.project', compact('project'));
    }
}
