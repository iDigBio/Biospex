<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class ExpeditionStat extends Model
{
    use LadaCacheTrait;

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
