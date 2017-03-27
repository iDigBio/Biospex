<?php

namespace App\Models;

use Jenssegers\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class TranscriptionLocation extends Eloquent
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'transcription_locations';

    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Accepted attributes
     *
     * @var array
     */
    protected $fillable = [
        'classification_id',
        'project_id',
        'expedition_id',
        'state_province',
        'county',
        'state_county'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function panoptesTranscription()
    {
        return $this->belongsTo(PanoptesTranscription::class, 'classification_id', 'classification_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stateCounty()
    {
        return $this->belongsTo(StateCounty::class, 'state_county', 'state_county');
    }
}
