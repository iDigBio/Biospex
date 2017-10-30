<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\MongoDbException;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\SubjectContract;
use Exception;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use App\Services\Grid\JqGridJsonEncoder;
use MongoCollection;
use function MongoDB\BSON\toPHP;
use Response;
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
     * GridsController constructor.
     * @param JqGridJsonEncoder $grid
     * @param Request $request
     * @param Csv $csv
     */
    public function __construct(
        JqGridJsonEncoder $grid,
        Request $request,
        Csv $csv
    )
    {
        $this->grid = $grid;
        $this->request = $request;
        $this->csv = $csv;

        $this->projectId = (int) $this->request->route('projects');
        $this->expeditionId = (int) $this->request->route('expeditions');
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
     * @throws Exception
     */
    public function explore()
    {
        return $this->grid->encodeGridRequestedData($this->request->all(), $this->request->route()->getName(), $this->projectId, $this->expeditionId);
    }

    public function expeditionsShow()
    {
        return $this->grid->encodeGridRequestedData($this->request->all(), $this->request->route()->getName(), $this->projectId, $this->expeditionId);
    }

    public function expeditionsEdit()
    {
        return $this->grid->encodeGridRequestedData($this->request->all(), $this->request->route()->getName(), $this->projectId, $this->expeditionId);
    }

    public function expeditionsCreate()
    {
        return $this->grid->encodeGridRequestedData($this->request->all(), $this->request->route()->getName(), $this->projectId, $this->expeditionId);
    }

    /**
     * @param $projectId
     * @param null $expeditionId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws MongoDbException
     */
    public function export($projectId, $expeditionId = null)
    {
        try
        {
            $databaseManager = app(DatabaseManager::class);
            $client = $databaseManager->connection('mongodb')->getMongoClient();
            $collection = $client->{config('database.connections.mongodb.database')}->subjects;

            $query = null === $expeditionId ?
                ['project_id' => (int) $projectId] :
                ['project_id' => (int) $projectId, 'expedition_ids' => (int) $expeditionId];

            $docs = $collection->find($query);
            $docs->setTypeMap([
                'array'    => 'array',
                'document' => 'array',
                'root'     => 'array'
            ]);

            $filename = $expeditionId === null ? 'grid_export_' . $projectId . '.csv' : 'grid_export_' . $projectId . '-' . $expeditionId. '.csv';
            $temp = storage_path('scratch/' . $filename);
            $this->csv->writerCreateFromPath($temp);

            $i = 0;
            foreach ($docs as $doc)
            {
                unset($doc['_id'], $doc['occurrence']);
                $doc['expedition_ids'] = trim(implode(', ', $doc['expedition_ids']), ',');
                $doc['updated_at'] = mongodb_date_format($doc['updated_at'], 'Y-m-d H:i:s');
                $doc['created_at'] = mongodb_date_format($doc['created_at'], 'Y-m-d H:i:s');

                if ($i === 0)
                {
                    $this->csv->insertOne(array_keys($doc));
                }

                $this->csv->insertOne($doc);
                $i++;
            }
        }
        catch (Exception $e)
        {
            throw new MongoDbException($e);
        }

        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0'
            , 'Content-type' => 'text/csv'
            , 'Content-disposition' => 'attachment; filename="' . $filename . '"'
            , 'Expires' => '0'
            , 'Pragma' => 'public'
        ];

        return Response::download($temp, $filename, $headers);
    }


    public function delete(SubjectContract $subjectContract, $projectId)
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

        foreach ($ids as $id)
        {
            $subjectContract->delete($id);
        }


        return response()->json(['success']);

    }
}


