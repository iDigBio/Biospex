<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class TranscriptionLocation extends Model
{

    use LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'transcription_locations';

    /**
     * @inheritDoc
     */
    protected $connection = 'mysql';

    /**
     * @inheritDoc
     */
    protected $primaryKey = 'id';

    /**
     * @inheritDoc
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
        return $this->belongsTo(State::class, 'state_county', 'state_county');
    }
}
