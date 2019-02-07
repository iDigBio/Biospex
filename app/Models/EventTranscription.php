<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\HybridRelations;

class EventTranscription extends BaseEloquentModel
{
    use HybridRelations;

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
        'classification_id',
        'event_id',
        'team_id',
        'user_id',
    ];

    /**
     * Transcription relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function transcription()
    {
        return $this->hasOne(PanoptesTranscription::class,'_id', 'classification_id');
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
     * Event Team relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(EventTeam::class);
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
