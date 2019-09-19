<?php

namespace App\Models;

use App\Models\Traits\Presentable;
use App\Presenters\PanoptesProjectPresenter;

class PanoptesProject extends BaseEloquentModel
{

    use Presentable;

    /**
     * @inheritDoc
     */
    protected $table = 'panoptes_projects';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'project_id',
        'expedition_id',
        'panoptes_project_id',
        'panoptes_workflow_id',
        'subject_sets',
        'slug'
    ];

    /**
     * @var string
     */
    protected $presenter = PanoptesProjectPresenter::class;


    /**
     * Project relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Expedition relationship.
     *
     * @return mixed
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Mutator for subject_sets column.
     *
     * @param $value
     */
    public function setSubjectSetsAttribute($value)
    {
        $this->attributes['subject_sets'] = json_encode($value);
    }

    /**
     * Accessor for subjects column.
     *
     * @param $value
     * @return mixed
     */
    public function getSubjectSetsAttribute($value)
    {
        return json_decode($value);
    }

}
