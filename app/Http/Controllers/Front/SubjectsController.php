<?php namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Grid\JqGridJsonEncoder;
use App\Repositories\Contracts\Project;

class SubjectsController extends Controller
{
    /**
     * @var
     */
    protected $grid;

    /**
     * @var
     */
    protected $project;

    /**
     * Constructor.
     *
     * @param JqGridJsonEncoder $grid
     * @param Project $project
     */
    public function __construct(JqGridJsonEncoder $grid, Project $project)
    {
        $this->grid = $grid;
        $this->project = $project;
    }

    /**
     * Display subject page.
     *
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function index($projectId)
    {
        $project = $this->project->find($projectId);

        return view('subjects.show', compact('project'));
    }

    /**
     * Load grid model and column names
     */
    public function load()
    {
        return $this->grid->loadGridModel();
    }

    /**
     * Load grid data.
     *
     * @throws Exception
     */
    public function show()
    {
        $this->grid->encodeRequestedData(\Input::all());
    }

    /**
     * Store selected rows to respective expeditions.
     *
     * @return string
     */
    public function store()
    {
        return $this->grid->updateSelectedRows(\Route::input('expeditions'), \Input::all());
    }
}
