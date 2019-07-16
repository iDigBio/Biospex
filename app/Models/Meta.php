<?php 

namespace App\Models;

class Meta extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'metas';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'project_id',
        'xml'
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
}
