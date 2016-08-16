<?php 

namespace App\Models;

use Jenssegers\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\UuidTrait;

class Expedition extends Eloquent
{
    use SoftDeletes;
    use UuidTrait;

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
     * Handle model events.
     */
    public static function boot() {

        parent::boot();

        static::deleting(function ($model) {
            $model->title = $model->title . ':' . str_random();
            $model->save();
            $model->stat->delete();
            $model->nfnWorkflow->delete();
        });

        self::restored(function ($model)
        {
            $title = explode(':', $model->title);
            $model->title = $title[0];
            $model->save();
            $model->stat->restore();
            $model->nfnWorkflow->restore();
        });
    }
    
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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
     * Download relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    /**
     * Actor relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'actor_expedition')
            ->withPivot('id', 'expedition_id', 'actor_id', 'state', 'error', 'queued', 'completed', 'order')
            ->orderBy('order');
    }

    /**
     * NfnWorkflow relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function nfnWorkflow()
    {
        return $this->hasOne(NfnWorkflow::class);
    }

    /**
     * NfnClassifications relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function nfnClassifications()
    {
        return $this->hasManyThrough(NfnClassification::class, NfnWorkflow::class);
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
     * NfnClassifications last id.
     *
     * @return mixed
     */
    public function nfnClassificationsLastId()
    {
        return $this->hasManyThrough(NfnClassification::class, NfnWorkflow::class)
            ->selectRaw('MAX(classification_id) as last_id');
    }

    /**
     * NfnClassificationsLstId attribute.
     *
     * @return int
     */
    public function getNfnClassificationsLastIdAttribute()
    {
        $related = $this->getRelationValue('nfnClassificationsLastId')->first();

        return $related ? (int) $related->last_id : 0;
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

    /**
     * Get all expeditions for user.
     * 
     * @param $id
     * @return mixed
     */
    public function getAllExpeditions($id)
    {
        return $this->leftJoin('expedition_stats', 'expedition_stats.expedition_id', '=', 'expeditions.id')
            ->leftJoin('downloads', 'downloads.expedition_id', '=', 'expeditions.id')
            ->leftJoin('actor_expedition', 'actor_expedition.expedition_id', '=', 'expeditions.id')
            ->leftJoin('projects', 'projects.id', '=', 'expeditions.project_id')
            ->leftJoin('groups', 'groups.id', '=', 'projects.group_id')
            ->leftJoin('group_user', 'group_user.group_id', '=', 'groups.id')
            ->select(
                'expeditions.id as expedition_id',
                'expeditions.title as expedition_title',
                'expeditions.description as expedition_description',
                'expeditions.created_at as expedition_created_at',
                'expedition_stats.subject_count',
                'expedition_stats.transcriptions_total',
                'expedition_stats.transcriptions_completed',
                'expedition_stats.percent_completed',
                'downloads.id as downloads_id',
                'projects.id as project_id',
                'projects.title as project_title',
                'groups.id as group_id',
                'groups.name as group_name',
                'actor_expedition.id as actor_expedition_id')
            ->where('group_user.user_id', '=', $id)
            ->get();
    }
}
