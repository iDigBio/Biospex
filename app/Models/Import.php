<?php 

namespace App\Models;

class Import extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'imports';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'file',
        'error'
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
     * User relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
