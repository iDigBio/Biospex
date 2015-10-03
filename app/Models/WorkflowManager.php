<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToExpeditionTrait;

class WorkflowManager extends Model
{
    use BelongsToExpeditionTrait;

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

    /**
     * Fillable columns.
     * @var array
     */
    protected $fillable = [
        'expedition_uuid',
        'expedition_id',
        'stopped',
        'error',
        'queue'
    ];

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

    /**
     * Get workflow process by expedition id
     *
     * @param $id
     * @return mixed
     */
    public function findByExpeditionId($id)
    {
        return $this->expeditionid($id)->first();
    }

    /**
     * Get all with relationship.
     *
     * @param $with
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function allWith($with)
    {
        return $this->with($with)->get();
    }
}
