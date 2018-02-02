<?php

namespace App\Repositories\Eloquent;

use App\Models\Event as Model;
use App\Repositories\Interfaces\Event;

class EventRepository extends EloquentRepository implements Event
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
    public function create(array $attributes)
    {
        $this->create($attributes);

        $groups = collect($attributes['groups'])->reject(function ($resource) {
            return $this->filterOrDeleteResources($resource);
        })->map(function ($resource) {
            return new ProjectResourceModel($resource);
        });

        $project->resources()->saveMany($resources->all());

        if ($project) {
            $this->notifyActorContacts($project->id);

            Flash::success(trans('projects.project_created'));

            return $project;
        }

        Flash::error(trans('projects.project_save_error'));

        return false;
    }
}