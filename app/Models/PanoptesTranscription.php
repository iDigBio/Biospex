<?php

namespace App\Models;

use Jenssegers\Mongodb\Model as Eloquent;
use MongoDate;

class PanoptesTranscription extends Eloquent
{
    /**
     * Redefine connection to use mongodb
     */
    protected $connection = 'mongodb';

    /**
     * Set Collection
     */
    protected $collection = 'panoptes_transcriptions';

    /**
     * Set primary key
     */
    protected $primaryKey = '_id';

    /**
     * @var bool
     */
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
        return $this->belongsTo(Project::class, 'id', 'subject_projectId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class, 'id', 'subject_expeditionId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, '_id', 'subject_subjectId');
    }

    /**
     * Mutate finished_at date for MongoDb
     *
     * @param  string  $value
     * @return string
     */
    public function setClassificationFinishedAtAttribute($value)
    {
        $this->attributes['classification_finished_at'] = new MongoDate(strtotime($value));
    }

    /**
     * Return finished_at in usable format
     *
     * @param  string  $value
     * @return string
     */
    public function getClassificationFinishedAtAttribute($value)
    {
        return $value->toDateTime()->format('Y-m-d H:i:s');
    }

    /**
     * Mutate started_at for MongoDb
     *
     * @param  string  $value
     * @return string
     */
    public function setClassificationStartedAtAttribute($value)
    {
        $this->attributes['classification_started_at'] = new MongoDate(strtotime($value));
    }

    /**
     * Return started_at date in usable format
     *
     * @param  string  $value
     * @return string
     */
    public function getClassificationStartedAtAttribute($value)
    {
        return $value->toDateTime()->format('Y-m-d H:i:s');
    }
    
}