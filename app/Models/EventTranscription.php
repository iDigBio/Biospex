<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class EventTranscription extends Model
{
    use HybridRelations, LadaCacheTrait;

    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * @inheritDoc
     */
    protected $table = 'event_transcriptions';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'transcription_id',
        'event_id',
        'group_id',
        'user_id',
    ];

    /**
     * Transcription relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function transcription()
    {
        return $this->hasOne(PanoptesTranscription::class,'_id', 'transcription_id');
    }

    /**
     * Event relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Event Group relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(EventGroup::class);
    }

    /**
     * Event User relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(EventUser::class);
    }
}
