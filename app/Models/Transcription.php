<?php namespace App\Models;

use Jenssegers\Mongodb\Model as Eloquent;

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

    public function getCountByExpeditionId($expeditionId)
    {
        return $this->whereIn('expedition_ids', [$expeditionId])->count();
    }
}
