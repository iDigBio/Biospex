<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowManager extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'workflow_managers';

    /**
     * @var array
     */
    //protected $dates = ['deleted_at'];


    protected $fillable = [
        'expedition_id',
        'stopped'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Scope
     *
     * @param $query
     * @param $id
     * @return mixed
     */
    public function scopeExpeditionId($query, $id)
    {
        return $query->where('expedition_id', '=', $id);
    }
}
