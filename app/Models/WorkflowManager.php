<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowManager extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'workflow_managers';

    /**
     * Do not use timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'expedition_id',
        'stopped',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
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
