<?php 

namespace App\Models;

use Jenssegers\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\UuidTrait;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Expedition extends Eloquent
{
    use SoftDeletes, UuidTrait, SoftCascadeTrait;

    /**
     * Soft delete cascades.
     *
     * @var array
     */
    protected $softCascade = ['stat', 'nfnWorkflow', 'stat', 'workflowManager'];

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'expeditions';

    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Accepted attributes
     *
     * @var array
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

    public function nfnActor()
    {
        return $this->belongsToMany(Actor::class, 'actor_expedition')
            ->wherePivot('actor_id', 2)
            ->wherePivot('queued', 0);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function nfnWorkflow()
    {
        return $this->hasOne(NfnWorkflow::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function nfnClassifications()
    {
        return $this->hasManyThrough(NfnClassification::class, NfnWorkflow::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function panoptesTranscriptions()
    {
        return $this->hasMany(PanoptesTranscription::class, 'subject_expeditionId');
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

    /**
     * NfnClassifications relationship count.
     * @return mixed
     */
    public function nfnClassificationsCount()
    {
        return $this->hasManyThrough(NfnClassification::class, NfnWorkflow::class)
            ->selectRaw('nfn_workflow_id, count(*) as aggregate')->groupBy('nfn_workflow_id');
    }

    /**
     * NfnClassificationsCount attribute.
     *
     * @return int
     */
    public function getNfnClassificationsCountAttribute()
    {
        $related = $this->getRelationValue('nfnClassificationsCount')->first();

        return $related ? (int) $related->aggregate : 0;
    }

    /**
     * Find by uuid.
     *
     * @param $uuid
     * @return mixed
     */
    public function findByUuid($uuid)
    {
        return $this->where('uuid', pack('H*', str_replace('-', '', $uuid)))->get();
    }

    /**
     * Set uuid for binary storage.
     *
     * @param $value
     */
    public function setUuidAttribute($value)
    {
        $this->attributes['uuid'] = pack('H*', str_replace('-', '', $value));
    }

    /**
     * Return uuid in normal format.
     *
     * @param $value
     * @return string
     */
    public function getUuidAttribute($value)
    {
        if ($value === null) {
            return;
        }

        $uuid = bin2hex($value);

        return substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20);
    }
}
