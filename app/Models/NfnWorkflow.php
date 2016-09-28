<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NfnWorkflow extends Model
{

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nfn_workflows';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    protected $fillable = [
        'project_id',
        'expedition_id',
        'project',
        'workflow',
        'subject_sets'
    ];

    /**
     * Boot function to add model events
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model)
        {
            $model->classifications()->delete();
        });

        self::restored(function ($model)
        {
            $model->classifications()->delete();
        });
    }

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
     * Classifications relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classifications()
    {
        return $this->hasMany(NfnClassification::class);
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
