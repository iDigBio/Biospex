<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use MongoDB\BSON\UTCDateTime;

class PanoptesTranscription extends BaseMongoModel
{

    /**
     * Set Collection
     */
    protected $collection = 'panoptes_transcriptions';

    /**
     * OrderBy
     *
     * @var array
     */
    protected $orderBy = [[]];

    protected static function boot()
    {
        parent::boot();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'subject_projectId', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class, 'subject_expeditionId', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_subjectId', '_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function dashboard()
    {
        return $this->hasOne(PusherTranscription::class, 'classification_id', 'classification_id');
    }

    /**
     * Set project id.
     *
     * @param $value
     * @return int
     */
    public function setSubjectProjectIdAttribute($value)
    {
        return $this->attributes['subject_projectId'] = (int) $value;
    }

    /**
     * Set expedition id.
     *
     * @param $value
     * @return int
     */
    public function setSubjectExpeditionIdAttribute($value)
    {
        return $this->attributes['subject_expeditionId'] = (int) $value;
    }


    /**
     * Mutate finished_at date for MongoDb
     *
     * @param  string  $value
     */
    public function setClassificationFinishedAtAttribute($value)
    {
        $this->attributes['classification_finished_at'] = Carbon::parse($value);
    }


    /**
     * Mutate started_at for MongoDb
     *
     * @param  string  $value
     */
    public function setClassificationStartedAtAttribute($value)
    {
        $this->attributes['classification_started_at'] = Carbon::parse($value);
    }
}
