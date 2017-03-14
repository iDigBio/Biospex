<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpeditionStat extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'expedition_stats';

    /**
     * Accepted attributes
     *
     * @var array
     */
    protected $fillable = [
        'expedition_id',
        'subject_count',
        'transcriptions_total',
        'transcriptions_completed',
        'percentage_completed',
        'classifiction_process'
    ];

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }
}
