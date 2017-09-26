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
            $collection =$client->{config('database.connections.mongodb.database')}->subjects;

            $query = null === $expeditionId ?
                ['project_id' => (int) $projectId] :
                ['project_id' => (int) $projectId, 'expedition_ids' => (int) $expeditionId];

            $cursor = $collection->find($query);

            $filename = 'grid_export_' . $projectId . '.csv';
            $temp = storage_path('scratch/' . $filename);
            $this->csv->writerCreateFromPath($temp);

            $i = 0;
            while($cursor->hasNext())
            {
                $cursor->next();
                $record = $cursor->current();
                unset($record['_id'], $record['occurrence']);
                $record['expedition_ids'] = trim(implode(', ', $record['expedition_ids']), ',');
                $record['updated_at'] = date('Y-m-d H:i:s', $record['updated_at']->sec);
                $record['created_at'] = date('Y-m-d H:i:s', $record['created_at']->sec);

                if ($i === 0)
                {
                    $this->csv->insertOne(array_keys($record));
                }

                $this->csv->insertOne($record);
                $i++;
            }
        }
        catch (Exception $e)
        {
            throw new MongoDbException($e);
        }

        $headers = [
            'Content-type' => 'text/csv; charset=utf-8',
            'Content-disposition' => 'attachment; filename="'.$filename.'"'
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


