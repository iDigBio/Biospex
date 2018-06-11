<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class NfnWorkflow extends Model
{

    use LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'nfn_workflows';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'project_id',
        'expedition_id',
        'project',
        'workflow',
        'subject_sets'
    ];


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
     * Set the nfn_project to null if empty.
     *
     * @param  string $value
     * @return string
     */
    public function setProjectAttribute($value)
    {
        $this->attributes['project'] = empty($value) ? null : $value;
    }

    /**
     * Set the workflow to null if empty.
     *
     * @param  string $value
     * @return string
     */
    public function setWorkflowAttribute($value)
    {
        $this->attributes['workflow'] = empty($value) ? null : $value;
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
