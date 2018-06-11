<?php 

namespace App\Models;

use App\Models\Traits\UuidTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Expedition extends Model
{
    use UuidTrait, SoftCascadeTrait, SoftDeletes, HybridRelations, LadaCacheTrait;

    /**
     * Enable soft delete.
     *
     * @var boolean
     */
    protected $softDelete = true;

    /**
     * Soft delete cascades.
     *
     * @var array
     */
    protected $softCascade = ['stat', 'nfnWorkflow', 'workflowManager', 'downloads', 'exportQueue'];

    /**
     * @inheritDoc
     */
    protected $dates = ['deleted_at'];

    /**
     * @inheritDoc
     */
    protected $table = 'expeditions';

    /**
     * @inheritDoc
     */
    protected $connection = 'mysql';

    /**
     * @inheritDoc
     */
    protected $primaryKey = 'id';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'uuid',
        'project_id',
        'title',
        'description',
        'keywords'
    ];


    /**
     * Project relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * ExpeditionStat relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function stat()
    {
        return $this->hasOne(ExpeditionStat::class);
    }

    /**
     * Return expedition stat transcriptions have started.
     *
     * @return mixed
     */
    public function statWithTranscriptions()
    {
        return $this->hasOne(ExpeditionStat::class)->where('transcriptions_completed', '>', 0);
    }

    /**
     * Subject relationship.
     * $expedition->subjects()->attach($subject) adds expedition ids in subjects
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    /**
     * WorkflowManager relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function workflowManager()
    {
        return $this->hasOne(WorkflowManager::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    /**
     * @return mixed
     */
    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'actor_expedition')
            ->withPivot('id', 'expedition_id', 'actor_id', 'state', 'total', 'processed', 'error', 'queued', 'completed', 'order')
            ->orderBy('order')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function nfnActor()
    {
        $pivot = [
            'id',
            'expedition_id',
            'actor_id',
            'state',
            'total',
            'processed',
            'error',
            'queued',
            'completed',
            'order'
        ];

        return $this->belongsToMany(Actor::class, 'actor_expedition')
            ->withPivot($pivot)
            ->wherePivot('actor_id', 2);
    }

    /**
     * NfnClassificationsCount attribute.
     *
     * @return int
     */
    public function getNfnActorAttribute()
    {
        return $this->getRelationValue('nfnActor')->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function actor()
    {
        return $this->actors();
    }

    /**
     * NfnClassificationsCount attribute.
     *
     * @return int
     */
    public function getActorAttribute()
    {
        return $this->getRelationValue('actor')->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function nfnWorkflow()
    {
        return $this->hasOne(NfnWorkflow::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function panoptesTranscriptions()
    {
        return $this->hasMany(PanoptesTranscription::class, 'subject_expeditionId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dashboard()
    {
        return $this->hasMany(PusherTranscription::class, 'expedition_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function exportQueue()
    {
        return $this->hasOne(ExportQueue::class);
    }

    /**
     * Get counts attribute.
     *
     * @return int
     */
    public function getSubjectsCountAttribute()
    {
        return $this->subjects()->count();
    }

}
