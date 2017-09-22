<?php

namespace App\Models;

use App\Models\Traits\MongoDbTrait;
use Jenssegers\Mongodb\Eloquent\Model;

class PanoptesTranscription extends Model
{
    use MongoDbTrait;

    /**
     * @inheritDoc
     */
    protected $connection = 'mongodb';

    /**
     * Set Collection
     */
    protected $collection = 'panoptes_transcriptions';

    /**
     * @inheritDoc
     */
    protected $primaryKey = '_id';

    /**
     * @inheritDoc
     */
    public $incrementing = false;

    /**
     * @inheritDoc
     */
    protected $guarded = ['_id'];

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
        return $this->hasOne(WeDigBioDashboard::class, 'transcription_id', '_id');
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
     * @return string
     */
    public function setClassificationFinishedAtAttribute($value)
    {
        $this->attributes['classification_finished_at'] = $this->asMongoDate($value);
    }

    /**
     * Return finished_at in usable format
     *
     * @param  string  $value
     * @return string
     */
    public function getClassificationFinishedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    /**
     * Mutate started_at for MongoDb
     *
     * @param  string  $value
     * @return string
     */
    public function setClassificationStartedAtAttribute($value)
    {
        $this->attributes['classification_started_at'] = $this->asMongoDate($value);
    }

    /**
     * Return started_at date in usable format
     *
     * @param  string  $value
     * @return string
     */
    public function getClassificationStartedAtAttribute($value)
    {
        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }
}
