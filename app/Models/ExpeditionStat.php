<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ExpeditionStat extends BaseEloquentModel
{
    use SoftDeletes;

    /**
     * Enable soft delete.
     *
     * @var boolean
     */
    protected $softDelete = true;

    /**
     * @inheritDoc
     */
    protected $table = 'expedition_stats';

    /**
     * @inheritDoc
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
     * @inheritDoc
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
