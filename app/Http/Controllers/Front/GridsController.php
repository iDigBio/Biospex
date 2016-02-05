<?php

namespace Biospex\Http\Controllers\Front;

use Biospex\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Biospex\Repositories\Contracts\Header;
use Biospex\Services\Grid\JqGridJsonEncoder;
use Biospex\Repositories\Contracts\Project;
use Cartalyst\Sentry\Sentry;

class GridsController extends Controller
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
     * @var
     */
    protected $fields;

    /**
     * @var Sentry
     */
    protected $sentry;

    /**
     * @var HeaderInterface
     */
    protected $header;

    /**
     * @var int
     */
    protected $projectId;

    /**
     * @var int
     */
    protected $expeditionId;

    /**
     * @var string
     */
    protected $route;
    /**
     * @var Request
     */
    private $request;

    /**
     * Constructor.
     *
     * @param JqGridJsonEncoder $grid
     * @param ProjectInterface|Project $project
     * @param Sentry $sentry
     * @param HeaderInterface|Header $header
     * @param Request $request
     * @internal param UserGridFieldInterface $fields
     * @internal param Router $router
     */
    public function __construct(
        JqGridJsonEncoder $grid,
        Project $project,
        Sentry $sentry,
        Header $header,
        Request $request
    )
    {
        $this->grid = $grid;
        $this->project = $project;
        $this->sentry = $sentry;
        $this->header = $header;
        $this->request = $request;

        $this->projectId = (int) $this->request->route('projects');
        $this->expeditionId = (int) $this->request->route('expeditions');
        $this->route = $this->request->route()->getName();
    }

    /**
     * Load grid model and column names
     */
    public function load()
    {
        return $this->grid->loadGridModel($this->projectId, $this->route);
    }

    /**
     * Load grid data.
     *
     * @throws Exception
     */
    public function explore()
    {
        return $this->grid->encodeGridRequestedData($this->request->all(), $this->route, $this->projectId, $this->expeditionId);
    }

    public function expeditionsShow()
    {
        return $this->grid->encodeGridRequestedData($this->request->all(), $this->route, $this->projectId, $this->expeditionId);
    }

    public function expeditionsEdit()
    {
        return $this->grid->encodeGridRequestedData($this->request->all(), $this->route, $this->projectId, $this->expeditionId);
    }

    public function expeditionsCreate()
    {
        return $this->grid->encodeGridRequestedData($this->request->all(), $this->route, $this->projectId, $this->expeditionId);
    }
}


