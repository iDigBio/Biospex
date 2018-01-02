<?php  

namespace App\Repositories;

use App\Models\Workflow as Model;
use App\Interfaces\Workflow;

class WorkflowRepository extends EloquentRepository implements Workflow
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritdoc
     */
    public function getWorkflowSelect()
    {
        $results = ['--Select--'] + $this->model->where('enabled', '=',1)
                ->orderBy('title', 'asc')
                ->pluck('title', 'id')
                ->toArray();

        $this->resetModel();

        return $results;
    }
}

