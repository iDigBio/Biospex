<?php 

namespace App\Models;

class ExpeditionStat extends BaseEloquentModel
{

    /**
     * @inheritDoc
     */
    protected $table = 'expedition_stats';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'expedition_id',
        'local_subject_count',
        'subject_count',
        'transcriptions_total',
        'transcriptions_completed',
        'percentage_completed',
        'classifiction_process'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }
}
