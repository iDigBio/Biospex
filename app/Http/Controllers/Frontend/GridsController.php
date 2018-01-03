<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\GridExportCsvJob;
use App\Services\Model\SubjectService;
use App\Services\MongoDbService;
use Illuminate\Http\Request;
use App\Services\Grid\JqGridJsonEncoder;
use App\Services\Csv\Csv;

class GridsController extends Controller
{

    /**
     * @var
     */
    public $grid;

    /**
     * @var
     */
    public $fields;

    /**
     * @var int
     */
    public $projectId;

    /**
     * @var int
     */
    public $expeditionId;

    /**
     * @var string
     */
    public $route;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var Csv
     */
    public $csv;
    /**
     * @var MongoDbService
     */
    private $mongoDbService;

    /**
     * GridsController constructor.
     * @param JqGridJsonEncoder $grid
     * @param Request $request
     * @param Csv $csv
     * @param MongoDbService $mongoDbService
     */
    public function __construct(
        JqGridJsonEncoder $grid,
        Request $request,
        Csv $csv,
        MongoDbService $mongoDbService
    )
    {
        $this->grid = $grid;
        $this->request = $request;
        $this->csv = $csv;

        $this->projectId = (int) $this->request->route('projects');
        $this->expeditionId = (int) $this->request->route('expeditions');
        $this->mongoDbService = $mongoDbService;
    }

    /**
     * Load grid model and column names
     */
    public function load()
    {
        return $this->grid->loadGridModel($this->projectId, $this->request->route()->getName());
    }

    /**
     * Load grid data.
     *
     * @return string
     */
    public function explore()
    {
        try
        {
            return $this->grid->encodeGridRequestedData($this->request->all(), $this->request->route()->getName(), $this->projectId, $this->expeditionId);
        }
        catch (\Exception $e)
        {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions.
     *
     * @return string
     */
    public function expeditionsShow()
    {
        try
        {
            return $this->grid->encodeGridRequestedData($this->request->all(), $this->request->route()->getName(), $this->projectId, $this->expeditionId);
        }
        catch (\Exception $e)
        {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions edit.
     *
     * @return string
     */
    public function expeditionsEdit()
    {
        try
        {
            return $this->grid->encodeGridRequestedData($this->request->all(), $this->request->route()->getName(), $this->projectId, $this->expeditionId);
        }
        catch (\Exception $e)
        {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions create.
     *
     * @return string
     */
    public function expeditionsCreate()
    {
        try
        {
            return $this->grid->encodeGridRequestedData($this->request->all(), $this->request->route()->getName(), $this->projectId, $this->expeditionId);
        }
        catch (\Exception $e)
        {
            return response($e->getMessage(), 404);
        }
    }

    /**
     * Export csv from grid button.
     *
     * @param $projectId
     * @param null $expeditionId
     */
    public function export($projectId, $expeditionId = null)
    {
        GridExportCsvJob::dispatch(\Auth::user(), $projectId, $expeditionId);

        return;
    }


    /**
     * Delete subject if not part of expedition process.
     *
     * @param SubjectService $subjectService
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(SubjectService $subjectService)
    {
        if ( ! $this->request->ajax())
        {
            return response()->json(['error' => 'Delete must be performed via ajax.'], 404);
        }

        if ( ! $this->request->get('oper'))
        {
            return response()->json(['error' => 'Only delete operation allowed.'], 404);
        }

        $ids = explode(',', $this->request->get('id'));

        $subjectService->deleteSubjects($ids);

        return response()->json(['success']);

    }
}


