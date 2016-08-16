<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class NfnClassification extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nfn_classifications';

    protected $fillable = [
        'nfn_workflow_id',
        'classification_id',
        'subjects',
        'started_at',
        'finished_at'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * NfnWorkflow relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nfnWorkflow()
    {
        return $this->belongsTo(NfnWorkflow::class);
    }

    /**
     * Mutator for subjects column.
     *
     * @param $value
     */
    public function setSubjectsAttribute($value)
    {
        $this->attributes['subjects'] = json_encode($value);
    }

    /**
     * Accessor for subjects column.
     *
     * @param $value
     * @return mixed
     */
    public function getSubjectsAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Return classification count grouped by finished_at date.
     *
     * @param $workflow
     * @return mixed
     */
    public function getExpeditionsGroupByFinishedAt($workflow)
    {
        return $this->selectRaw('DATE_FORMAT(finished_at, \'%Y-%m-%d\') as finished_at, count(*) as total')
            ->where('nfn_workflow_id', $workflow)
            ->groupBy(DB::raw('DATE_FORMAT(finished_at, \'%Y-%m-%d\')'))
            ->orderBy('finished_at')
            ->get();
    }
}
