<?php namespace App\Models;

use Jenssegers\Mongodb\Model as Eloquent;
use MongoDate;

class Transcription extends Eloquent
{
    /**
     * Redefine connection to use mongodb
     */
    protected $connection = 'mongodb';

    /**
     * Set primary key
     */
    protected $primaryKey = '_id';

    public $incrementing = false;

    /**
     * set guarded properties
     */
    protected $guarded = ['_id'];

    /**
     * OrderBy
     *
     * @var array
     */
    protected $orderBy = [[]];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Mutate finished_at date for MongoDb
     *
     * @param  string  $value
     * @return string
     */
    public function setFinishedAtAttribute($value)
    {
        $this->attributes['finished_at'] = new MongoDate(strtotime($value));
    }

    /**
     * Return finished_at in usable format
     *
     * @param  string  $value
     * @return string
     */
    public function getFinishedAtAttribute($value)
    {
        return $value->toDateTime()->format('Y-m-d H:i:s');
    }

    /**
     * Mutate started_at for MongoDb
     *
     * @param  string  $value
     * @return string
     */
    public function setStartedAtAttribute($value)
    {
        $this->attributes['started_at'] = new MongoDate(strtotime($value));
    }

    /**
     * Return started_at date in usable format
     *
     * @param  string  $value
     * @return string
     */
    public function getStartedAtAttribute($value)
    {
        return $value->toDateTime()->format('Y-m-d H:i:s');
    }

    /**
     * Get count using expedition id.
     * 
     * @param $expeditionId
     * @return mixed
     */
    public function getCountByExpeditionId($expeditionId)
    {
        return $this->where('expedition_id', $expeditionId)->count();
    }

    /**
     * Get earliest finished date of transcriptions.
     * 
     * @param $project_id
     * @param $expedition_id
     * @return mixed
     */
    public function getEarliestDate($project_id, $expedition_id)
    {
        return $this->where('project_id', $project_id)->where('expedition_id', $expedition_id)->orderBy('finished_at', 'asc')->first();
    }
}
