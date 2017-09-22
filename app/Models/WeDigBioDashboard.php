<?php

namespace App\Models;

use App\Models\Traits\MongoDbTrait;
use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;

class WeDigBioDashboard extends Model
{
    use MongoDbTrait;

    /**
     * @inheritDoc
     */
    protected $connection = 'mongodb';

    /**
     * Set Collection
     */
    protected $collection = 'wedigbio_dashboard';

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
     * @inheritDoc
     */
    protected $dates = ['created_at', 'updated_at', 'timestamp'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class, 'expedition_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transcription()
    {
        return $this->belongsTo(PanoptesTranscription::class, 'transcription_id', '_id');
    }

    /**
     * Set expedition_id attribute to integer.
     *
     * @param $value
     */
    public function setExpeditionIdAttribute($value)
    {
        $this->attributes['expedition_id'] = (int) $value;
    }

    /**
     * Set transcript as mongo id.
     *
     * @param $value
     * @return \MongoDB\BSON\ObjectID
     */
    public function setTranscriptionIdAttribute($value)
    {
        $this->attributes['transcription_id'] = $this->asMongoID($value);
    }

    /**
     * Get transcript id.
     *
     * @param $value
     * @return mixed
     */
    public function getTranscriptIdAttribute($value)
    {
        return $this->getIdAttribute($value);
    }

    /**
     * Mutate finished_at date for MongoDb
     *
     * @param  string  $value
     * @return string
     */
    public function setTimestampAttribute($value)
    {
        // new MongoDate(strtotime($value))
        $this->attributes['timestamp'] = $this->asMongoDate($value);
    }

    /**
     * Return finished_at in usable format
     *
     * @param  string  $value
     * @return string
     */
    public function getTimestampAttribute($value)
    {
        return $value->toDateTime()->format('Y-m-d\TH:i:s\Z');
    }
}
