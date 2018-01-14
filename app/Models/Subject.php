<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Subject extends Model
{
    /**
     * @inheritDoc
     */
    protected $connection = 'mongodb';

    /**
     * @inheritDoc
     */
    protected $primaryKey = '_id';

    /**
     * @inheritDoc
     */
    public $incrementing = false;

    /**
     * @inheritDoc
     */
    protected $guarded = ['_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function panoptesTranscriptions()
    {
        return $this->hasMany(PanoptesTranscription::class, 'subject_subjectId');
    }

    /**
     * @return \Jenssegers\Mongodb\Relations\EmbedsOne
     */
    public function occurrence()
    {
        return $this->embedsOne(Occurrence::class, 'occurrence');
    }

    /**
     * Set project id attribute.
     *
     * @param $value
     * @return int
     */
    public function setProjectId($value)
    {
        return $this->attributes['project_id'] = (int) $value;
    }

    /**
     * @param $query
     * @param $projectId
     * @return mixed
     */
    public function scopeProjectId($query, $projectId)
    {
        return $query->where('project_id', (int) $projectId);
    }

    /**
     * @param $query
     * @param $subjectId
     * @return mixed
     */
    public function scopeSubjectId($query, $subjectId)
    {
        return $query->where('_id', $subjectId);
    }

}
